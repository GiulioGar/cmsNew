<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
	<meta name="author" content="AdminKit">
	<meta name="keywords" content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="img/icons/icon-48x48.png" />

	<link rel="canonical" href="https://demo-basic.adminkit.io/pages-sign-in.html" />

	<title>Accesso | Gestionale Interactive</title>

	<link href="css/app.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="text-center mt-4">
							<h1 class="h2">Bentornato</h1>
							<p class="lead">
								Effettua il log in per procedere!
							</p>
						</div>

						<div class="card">
							<div class="card-body">
								<div class="m-sm-3">
									<form action="login.php" method="POST">
										<?php if (isset($_GET['error'])): ?>
											<div class="alert alert-danger">
												<?php 
												if ($_GET['error'] == 1) {
													echo "Nome utente o password errati!";
												} elseif ($_GET['error'] == 2) {
													echo "Si è verificato un errore, riprova.";
												}
												?>
											</div>
										<?php endif; ?>
									
										<div class="mb-3">
											<label class="form-label">Nome utente</label>
											<input class="form-control form-control-lg" type="text" name="username" placeholder="Inserisci il tuo nome utente" required />
										</div>
										<div class="mb-3">
											<label class="form-label">Password</label>
											<div class="input-group">
												<input class="form-control form-control-lg" type="password" id="password" name="password" placeholder="Inserisci la tua password" required />
												<button type="button" class="btn btn-outline-secondary" id="togglePassword">
													👁
												</button>
											</div>
										</div>
										<div class="d-grid gap-2 mt-3">
											<button type="submit" class="btn btn-lg btn-primary">Accedi</button>
										</div>
									</form>
									
								</div>
							</div>
						</div>
						<div class="text-center mb-3">
							Se non hai un account contatta l'amministratore
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="js/app.js"></script>

</body>

<script>
document.getElementById("togglePassword").addEventListener("click", function() {
    var passwordField = document.getElementById("password");
    if (passwordField.type === "password") {
        passwordField.type = "text";
        this.textContent = "⭕"; // Cambia icona
    } else {
        passwordField.type = "password";
        this.textContent = "👀"; // Cambia icona
    }
});
</script>


</html>