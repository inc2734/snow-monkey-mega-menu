document.addEventListener(
	'DOMContentLoaded',
	() => {
		const menus = document.querySelectorAll('.l-header:not(.l-header--left) .snow-monkey-mega-menu > .c-navbar__submenu');
		const container = document.querySelector('.l-container');
		if (! container || 1 > menus.length) {
			return;
		}

		const init = () => {
			const containerWidth = container.offsetWidth;

			[].slice.call(menus).forEach((menu) => {
				menu.style.left = 0;
				const menuWidth = menu.offsetWidth;
				const menuLeft = menu.getBoundingClientRect().left;
				const center = (containerWidth - menuWidth) / 2;
				const newMenuLeft = center - menuLeft;
				menu.style.left = `${ newMenuLeft }px`;
			});
		};

		init();
		window.addEventListener('resize', () => init(), true);
	},
	true
);
