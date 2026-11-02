const wt_timer = new Worker('./wt_timer.js');

const cb_registry = new Map();
let uid_counter = 0;

wt_timer.onmessage = function(event) {
	if (!event.data) return;
	const { event: workerEvent, id } = event.data;

	if (workerEvent === 'TIMEOUT_TRIGGER') {
		const record = cb_registry.get(id);
		if (!record) return;

		// clean up registry to prevent memory leaks
		cb_registry.delete(id);

		if (typeof record.callback === 'function') {
			record.callback(...record.args);
		} else if (typeof record.callback === 'string') {
			try {
				new Function(record.callback)();
			} catch (e) {
				console.error("Error executing string timeout:", e);
			}
		} else {
			console.warn("setWTTimeout received an invalid callback type:", typeof record.callback);
		}
	}
};

function setWTTimeout(callback, delay, ...args) {
	// if no callback is provided, don't register anything
	if (callback === undefined || callback === null)
		{
		console.error("setWTTimeout() called without a callback.");
		return 0;
		}

	const id = ++uid_counter;
	
	cb_registry.set(id, { 
		callback: callback, 
		args: args || [] 
	});

	// make sure delay is an interger or 0 if NaN
	delay = parseInt(delay, 10);

	wt_timer.postMessage({
		command: 'START',
		id: id,
		delay: isNaN(delay) ? 0 : delay
	});

	return id;
}

function clearWTTimeout(id) {
	if (!id) 
		{
		console.error("clearWTTimeout() called without an id.");
		return;
		}
	if (cb_registry.has(id)) {
		cb_registry.delete(id);
		wt_timer.postMessage({
			command: 'STOP',
			id: id
		});
	}
}

function setWTInterval(callback, delay, ...args) {
	if (callback === undefined || callback === null) return 0;

	const id = ++uid_counter;
	const cleanDelay = parseInt(delay, 10);
	const safeDelay = isNaN(cleanDelay) ? 0 : cleanDelay;

	// create a execution loop inside the registry
	const intervalLoop = () => 
		{
		if (typeof callback === 'function') 
			{
			callback(...args);
			} else if (typeof callback === 'string') 
			{
			try { new Function(callback)(); } catch (e) { console.error(e); }
			}

		// schedule the next pass through the worker
		wt_timer.postMessage({ command: 'START', id: id, delay: safeDelay });
		};

	// register the loop and throw the initial worker signal
	cb_registry.set(id, { callback: intervalLoop, args: [] });
	wt_timer.postMessage({ command: 'START', id: id, delay: safeDelay });

	return id;
}

function clearWTInterval(id) {
	if (!id)
		{
		console.error("clearWTInterval() called without an id.");
		return;
		}
	if (cb_registry.has(id)) {
		cb_registry.delete(id);
		wt_timer.postMessage({
			command: 'STOP',
			id: id
		});
	}
}

