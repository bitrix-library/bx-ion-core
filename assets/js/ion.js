/**
 * Плавающая панель администратора 1С-Битрикс
 */
document.addEventListener('DOMContentLoaded', event => {
	const path = document.location.pathname;
	const isAdminPath = RegExp('^\/bitrix\/admin\/(.*)').test(path);
	const el = document.getElementById('bx-panel');

	if (el === undefined || el === null || isAdminPath) {
		return false;
	}

	el.parentElement.style.setProperty('display', 'block');
	el.parentElement.style.setProperty('transition', 'all 0s');
	el.style.setProperty('position', 'fixed', 'important');
	el.style.setProperty('transition', 'all 0s');

	const bxPanelPositionTop = BX.getCookie('bx-panel-position-top');
	const bxPanelPositionLeft = BX.getCookie('bx-panel-position-left');

	if (bxPanelPositionTop <= document.documentElement.clientHeight - el.offsetHeight + 'px'
		&& bxPanelPositionTop >= '0px') {
		el.style.top = bxPanelPositionTop;
	}

	if (bxPanelPositionLeft <= document.documentElement.clientWidth - el.offsetWidth + 'px'
		&& bxPanelPositionLeft >= '0px') {
		el.style.left = bxPanelPositionLeft;
	}

	el.onmousedown = e => {
		if (e.target.onmousedown !== null) {
			return false;
		}

		let start_pos_x = e.clientX;
		let start_pos_y = e.clientY;

		document.onmouseup = () => {
			document.onmouseup = null;
			document.onmousemove = null;

			return false;
		};

		document.onmousemove = e => {
			let new_pos_x = start_pos_x - e.clientX;
			let new_pos_y = start_pos_y - e.clientY;
			start_pos_x = e.clientX;
			start_pos_y = e.clientY;

			if (el.offsetTop - new_pos_y <= document.documentElement.clientHeight - el.offsetHeight
				&& el.offsetTop - new_pos_y >= 0) {
				const top = el.offsetTop - new_pos_y + 'px';

				el.style.setProperty('top', top);

				BX.setCookie('bx-panel-position-top', top, {
					expires: 3600 * 24,
					path: '/'
				});
			}

			if (el.offsetLeft - new_pos_x <= document.documentElement.clientWidth - el.offsetWidth
				&& el.offsetLeft - new_pos_x >= 0) {
				const left = el.offsetLeft - new_pos_x + 'px';

				el.style.setProperty('left', left);

				BX.setCookie('bx-panel-position-left', left, {
					expires: 3600 * 24,
					path: '/'
				});
			}

			return false;
		};

		return false;
	};
});

/**
 * Масштабируемая панель администратора 1С-Битрикс
 */
document.addEventListener('DOMContentLoaded', event => {
	const path = document.location.pathname;
	const isAdminPath = RegExp('^\/bitrix\/admin\/(.*)').test(path);
	const el = document.getElementById('bx-panel');

	if (el === undefined || el === null || isAdminPath) {
		return false;
	}

	const resizerRB = document.createElement('div');
	resizerRB.style =
		'width: 12px !important;' +
		'height: 12px !important;' +
		'background: cyan !important;' +
		'position: absolute !important;' +
		'right: -6px !important;' +
		'bottom: -6px !important;' +
		'cursor: se-resize;' +
		'z-index: 1000;' +
		'border-radius: 100%;';

	const bxPanelSizeWidth = BX.getCookie('bx-panel-size-width');

	if (bxPanelSizeWidth >= '0px') {
		el.style.setProperty('width', bxPanelSizeWidth, 'important');
	}

	resizerRB.onmousedown = e => {
		let start_pos_x = e.clientX;

		document.onmouseup = () => {
			document.onmouseup = null;
			document.onmousemove = null;

			return false;
		};

		document.onmousemove = e => {
			let new_pos_x = start_pos_x - e.clientX;
			start_pos_x = e.clientX;

			const width = el.clientWidth - new_pos_x + 'px';

			el.style.setProperty('width', width, 'important');

			BX.setCookie('bx-panel-size-width', width, {
				expires: 3600 * 24,
				path: '/'
			});

			return false;
		};

		return false;
	};

	el.onmouseenter = e => {
		el.appendChild(resizerRB);
	};

	el.onmouseleave = e => {
		el.removeChild(resizerRB);
	};
});
