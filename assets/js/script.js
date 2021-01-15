(function() {
	const nav = document.querySelector('main nav');
	const methods = document.querySelector('.sidebar .methods');

	if(nav && methods) {
		methods.appendChild(nav.cloneNode(true));
	}

	const open = document.querySelector('.open');
	const close = document.querySelector('.close');
	const sidebar = document.querySelector('.sidebar');
	const content = document.querySelector('.main-content');

	open.addEventListener('click', function() {
		open.classList.toggle('show');
		close.classList.toggle('show');
		sidebar.classList.toggle('show');
	});

	close.addEventListener('click', function() {
		sidebar.classList.toggle('show');
		close.classList.toggle('show');
		open.classList.toggle('show');
	});

	const fcn = function() {
		sidebar.classList.remove('show');
		content.classList.remove('show');
		close.classList.remove('show');
		open.classList.add('show');
	};

	sidebar.addEventListener('click', fcn);
	content.addEventListener('click', fcn);
})();
