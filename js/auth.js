export const setupAuth = () => {
	// Mostrar modal de login
	document.querySelector('#login')?.addEventListener('click', function(e) {
		e.preventDefault();
		document.getElementById('loginModal').style.display = 'block';
	});

	// Cerrar modal de login
	document.getElementById('closeLoginModal')?.addEventListener('click', function() {
		document.getElementById('loginModal').style.display = 'none';
		document.getElementById('loginMessage').textContent = '';
	});

	// Envío del formulario de login por AJAX
	document.getElementById('loginForm')?.addEventListener('submit', function(e) {
		e.preventDefault();

		const login = document.getElementById('login').value;
		const password = document.getElementById('password').value;
		////ojo cambiar ahora se utilizara csrf
		fetch('login.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: `login=${encodeURIComponent(login)}&password=${encodeURIComponent(password)}`
			})
			.then(response => response.text())
			.then(data => {
				const mensaje = document.getElementById('loginMessage');
				if (data.includes('Bienvenido')) {
					mensaje.textContent = data;
					mensaje.style.color = 'green';
					setTimeout(() => {
						document.getElementById('loginModal').style.display = 'none';
					}, 1500);
				} else {
					mensaje.textContent = data;
					mensaje.style.color = 'red';
				}
			})
			.catch(error => {
				console.error('Error:', error);
			});
	});
};