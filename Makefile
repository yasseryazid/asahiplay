HOST=asahi.test

up:            ## build & jalankan
	docker compose up -d --build
	@echo "Pantau: docker compose logs -f app   → buka http://$(HOST)"
hosts:         ## tambah alias hostname lokal (butuh sudo)
	@grep -q " $(HOST)" /etc/hosts || echo "127.0.0.1 $(HOST)" | sudo tee -a /etc/hosts
test:
	docker compose exec app php artisan test
artisan:
	docker compose exec app php artisan $(c)
down:
	docker compose down
