#!/usr/bin/env bash
# Dynamic SVN author mapping for git-svn.
# Usage: git svn will call this with a single argument: the SVN username.
# Strategy:
#   1. If username exists in .github/svn-authors.txt (format: name = Real Name <email>) output mapped value.
#   2. Otherwise synthesize a deterministic placeholder: "Capitalized Username <username@svn.local>".
# This prevents git-svn from aborting on unmapped authors while still allowing
# you to curate real entries incrementally in the svn-authors.txt file.

set -euo pipefail
USER_NAME="${1:-}"
if [ -z "$USER_NAME" ]; then
  echo "Unknown <unknown@svn.local>"
  exit 0
fi

AUTHORS_FILE=".github/svn-authors.txt"

if [ -f "$AUTHORS_FILE" ]; then
  # Exact match at start of line followed by optional spaces then '='
  if grep -E "^${USER_NAME}[[:space:]]*=" "$AUTHORS_FILE" >/dev/null 2>&1; then
    # Extract right-hand side (after = and optional spaces)
    sed -n -E "s/^${USER_NAME}[[:space:]]*=[[:space:]]*(.*)$/\1/p" "$AUTHORS_FILE"
    exit 0
  fi
fi

# Synthesize: replace underscores with spaces for display name, capitalize first letter only.
DISPLAY_NAME="${USER_NAME//_/ }"
# Simple capitalization (leave rest as-is)
FIRST_CHAR="${DISPLAY_NAME:0:1}"
REST="${DISPLAY_NAME:1}"
DISPLAY_NAME="${FIRST_CHAR^^}${REST}"
echo "${DISPLAY_NAME} <${USER_NAME}@svn.local>"
