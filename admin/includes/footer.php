<footer class="footer">
				<div class="container-fluid">
					<div class="row text-muted">
						<div class="col-6 text-start">
							<p class="mb-0">
								<a class="text-muted" href="https://adminkit.io/" target="_blank"><strong>AdminKit</strong></a> - <a class="text-muted" href="https://adminkit.io/" target="_blank"><strong>Bootstrap Admin Template</strong></a>								&copy;
							</p>
						</div>
						<div class="col-6 text-end">
							<ul class="list-inline">
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Support</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Help Center</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Privacy</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Terms</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

	<script src="js/app.js"></script>


	

<script>
function updateRequests() {
    fetch('fetch_rewards.php')
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                document.getElementById("totalRequests").innerText = data.total;
                document.getElementById("totalRequestsText").innerText = data.total;
                document.getElementById("amazonRequests").innerText = data.amazon;
                document.getElementById("paypalRequests").innerText = data.paypal;
            }
        })
        .catch(error => console.error('Errore nel caricamento delle richieste:', error));
}

// Aggiorna i dati ogni 10 secondi
setInterval(updateRequests, 10000);

// Aggiorna i dati all'avvio della pagina
updateRequests();
</script>


</body>

</html>
