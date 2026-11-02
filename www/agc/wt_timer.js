const activeTimeouts = new Map();

self.onmessage = function(event) {
	if (!event.data) return;

	const { command, id, delay } = event.data;

	if (command === 'START') {
		if (activeTimeouts.has(id)) {
			clearTimeout(activeTimeouts.get(id));
		}

		const timerId = setTimeout(() => {
			self.postMessage({ event: 'TIMEOUT_TRIGGER', id });
			activeTimeouts.delete(id);
		}, delay ?? 0);

		activeTimeouts.set(id, timerId);
	} 

	else if (command === 'STOP') {
		if (activeTimeouts.has(id)) {
			clearTimeout(activeTimeouts.get(id));
			activeTimeouts.delete(id);
		}
	}
};
