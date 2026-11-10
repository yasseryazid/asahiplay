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

# --- Deploy produksi (server `yay`): satu perintah dari Mac ---
SERVER=yay
APPDIR=/opt/asahiplay
deploy:        ## push kode, kirim data IKSC (tidak ada di git), lalu update server
	git push origin main
	rsync -a database/seeders/data/iksc*.json $(SERVER):$(APPDIR)/database/seeders/data/
	rsync -a storage/app/banks/iksc* $(SERVER):$(APPDIR)/storage/app/banks/
	ssh $(SERVER) 'cd $(APPDIR) && chown -R 82:82 storage/app/banks && ./deploy.sh'
