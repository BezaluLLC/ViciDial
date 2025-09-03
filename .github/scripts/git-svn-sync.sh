#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

SVN_URL="${SVN_TRUNK_URL:-svn://svn.eflo.net:3690/agc_2-X/trunk}"
TARGET_BRANCH="${TARGET_BRANCH:-upstream-nightly}"
IMPORT_DIR="import"
AUTHORS_FILE=".github/svn-authors.txt"
AUTHORS_PROG="$(pwd)/.github/scripts/svn-authors-prog.sh"

echo "[info] Starting SVN -> Git sync"
echo "[info] SVN URL: $SVN_URL"
echo "[info] Target branch: $TARGET_BRANCH"

if [ ! -d "$IMPORT_DIR" ]; then
  mkdir -p "$IMPORT_DIR"
fi

if [ ! -d "$IMPORT_DIR/.git" ]; then
  echo "[info] No existing git-svn clone cache restored; performing initial clone (this can take a while)."
  if [ ! -x "$AUTHORS_PROG" ]; then
    echo "[warn] Authors prog $AUTHORS_PROG not executable; attempting to chmod +x"
    chmod +x "$AUTHORS_PROG" || true
  fi
  echo "[info] Using dynamic authors program: $AUTHORS_PROG"
  git svn clone "$SVN_URL" "$IMPORT_DIR" --authors-prog="$AUTHORS_PROG" -q
else
  echo "[info] Existing git-svn clone detected; fetching incremental updates."
  (cd "$IMPORT_DIR" && git svn fetch -q)
fi

cd "$IMPORT_DIR"

# Ensure we have the latest mapping if authors file added later
# No need to manually update mapping; authors-prog handles dynamic entries.

# Determine the git-svn ref for trunk (usually refs/remotes/git-svn)
GIT_SVN_REF="refs/remotes/git-svn"
if ! git show-ref --quiet "$GIT_SVN_REF"; then
  echo "[error] Expected git-svn ref $GIT_SVN_REF not found." >&2
  exit 1
fi

LATEST_SVN_REV=$(git log -1 --pretty=format:%s "$GIT_SVN_REF" | sed -n 's/.*@r\([0-9]\+\).*/\1/p' || true)
echo "[info] Latest imported SVN revision (best-effort parse): ${LATEST_SVN_REV:-unknown}" 
# Improved attempt: parse git-svn-id trailer if previous method failed
if [ -z "${LATEST_SVN_REV:-}" ] || [ "${LATEST_SVN_REV}" = "unknown" ]; then
  LATEST_SVN_REV=$(git log -1 "$GIT_SVN_REF" --pretty=format:%b | sed -n 's/.*@\([0-9]\+\) .*/\1/p' | head -n1 || true)
  if [ -n "${LATEST_SVN_REV:-}" ]; then
    echo "[info] Parsed SVN revision from body: $LATEST_SVN_REV"
  fi
fi

# Map/fast-forward local branch to svn ref
git update-ref "refs/heads/$TARGET_BRANCH" "$GIT_SVN_REF"

# Ensure origin remote exists (actions/checkout sets it with credentials header already)
if ! git remote get-url origin >/dev/null 2>&1; then
  git remote add origin "https://github.com/${GITHUB_REPOSITORY}.git"
fi

# (Re)configure auth header if token available (idempotent)
if [ -n "${GITHUB_TOKEN:-}" ]; then
  AUTH_HEADER=$(printf "x-access-token:%s" "$GITHUB_TOKEN" | base64 -w0 2>/dev/null || printf "x-access-token:%s" "$GITHUB_TOKEN" | base64)
  git config http.https://github.com/.extraheader "Authorization: basic $AUTH_HEADER"
else
  echo "[warn] GITHUB_TOKEN not set; will attempt unauthenticated push then fallback." >&2
fi

# Fetch current remote branch (if exists) to compare
git fetch --no-tags --quiet origin "+refs/heads/$TARGET_BRANCH:refs/remotes/origin/$TARGET_BRANCH" || true
OLD_REMOTE_SHA=$(git rev-parse "refs/remotes/origin/$TARGET_BRANCH" 2>/dev/null || echo "")
NEW_LOCAL_SHA=$(git rev-parse "$GIT_SVN_REF")

# Export metadata for later workflow steps (before possible early exit)
if [ -n "${GITHUB_ENV:-}" ]; then
  echo "IMPORTED_SVN_REV=${LATEST_SVN_REV:-}" >> "$GITHUB_ENV" || true
  echo "SYNC_PREV_SHA=${OLD_REMOTE_SHA}" >> "$GITHUB_ENV" || true
  echo "SYNC_TARGET_SHA=${NEW_LOCAL_SHA}" >> "$GITHUB_ENV" || true
fi

if [ -n "$OLD_REMOTE_SHA" ] && [ "$OLD_REMOTE_SHA" = "$NEW_LOCAL_SHA" ]; then
  echo "[info] No new SVN revisions. Remote branch already up to date (SHA $NEW_LOCAL_SHA). Skipping push."
  exit 0
fi

git config user.name "github-actions[bot]"
git config user.email "41898282+github-actions[bot]@users.noreply.github.com"

echo "[info] Pushing branch $TARGET_BRANCH to GitHub (new SHA $NEW_LOCAL_SHA, old $OLD_REMOTE_SHA)"
if ! git push --force origin "refs/heads/$TARGET_BRANCH:$TARGET_BRANCH"; then
  echo "[warn] First push attempt failed. Retrying verbose..." >&2
  if ! git push --force --verbose origin "refs/heads/$TARGET_BRANCH:$TARGET_BRANCH"; then
    if [ -n "${GITHUB_TOKEN:-}" ]; then
      echo "[warn] Retrying with token-embedded remote URL." >&2
      TOKEN_REMOTE="https://${GITHUB_ACTOR:-x-access-token}:${GITHUB_TOKEN}@github.com/${GITHUB_REPOSITORY}.git"
      git remote set-url origin "$TOKEN_REMOTE"
      if ! git push --force origin "refs/heads/$TARGET_BRANCH:$TARGET_BRANCH"; then
        echo "[error] Push failed after all retries." >&2
        exit 1
      fi
      # Restore clean remote URL (credentials via extraheader remain)
      git remote set-url origin "https://github.com/${GITHUB_REPOSITORY}.git" || true
    else
      echo "[error] Push failed and no GITHUB_TOKEN available." >&2
      exit 1
    fi
  fi
fi

echo "[info] Sync complete"
