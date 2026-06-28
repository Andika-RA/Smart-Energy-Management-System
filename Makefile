.PHONY: help up down restart logs k8s-deploy k8s-status k8s-down

GREEN=\033[0;32m
NC=\033[0m # No Color

help:
	@echo "${GREEN}=== Smart City CLI ===${NC}"
	@echo "Gunakan 'make <target>' dimana <target> adalah salah satu dari:"
	@echo "  up           : Membangun dan menjalankan seluruh kontainer Docker (Latar belakang)"
	@echo "  down         : Mematikan dan menghapus seluruh kontainer Docker"
	@echo "  restart      : Mematikan lalu menjalankan ulang Docker"
	@echo "  logs         : Melihat log seluruh sistem secara real-time"
	@echo "  k8s-deploy   : Men-deploy seluruh manifest ke kluster Kubernetes"
	@echo "  k8s-status   : Mengecek status Pod, Service, dan HPA di kluster"
	@echo "  k8s-down     : Menghapus seluruh deployment dari Kubernetes"

# === DOCKER COMPOSE COMMANDS ===
up:
	@echo "${GREEN}Membangun dan menjalankan microservices Smart City...${NC}"
	docker compose up -d --build

down:
	@echo "${GREEN}Mematikan sistem dan membersihkan network...${NC}"
	docker compose down

restart: down up

logs:
	@echo "${GREEN}Menampilkan log (Tekan Ctrl+C untuk keluar)...${NC}"
	docker compose logs -f

# === KUBERNETES COMMANDS ===
k8s-deploy:
	@echo "${GREEN}Membangun image Docker lokal...${NC}"
	docker compose build
	@echo "${GREEN}Men-tag image agar sesuai dengan manifest...${NC}"
	docker tag smart-energy-management-system-api-gateway:latest smart-city-platform-api-gateway:latest
	docker tag smart-energy-management-system-citizen-service:latest smart-city-platform-php-citizen:latest
	docker tag smart-energy-management-system-grid-service:latest smart-city-platform-php-grid:latest
	docker tag smart-energy-management-system-power-service:latest smart-city-platform-php-power:latest
	docker tag smart-energy-management-system-oauth-server:latest smart-city-platform-oauth-server:latest
	docker tag smart-energy-management-system-python-ml:latest smart-city-platform-python-ml:latest
	@echo "${GREEN}Mengompresi image ke tarball...${NC}"
	docker save -o smartcity-images.tar \
		smart-city-platform-api-gateway:latest \
		smart-city-platform-php-citizen:latest \
		smart-city-platform-php-grid:latest \
		smart-city-platform-php-power:latest \
		smart-city-platform-oauth-server:latest \
		smart-city-platform-python-ml:latest
	@echo "${GREEN}Menyalin tarball ke container k3d...${NC}"
	docker cp smartcity-images.tar k3d-smartplatform-server-0:/tmp/smartcity-images.tar
	@echo "${GREEN}Mengimpor image langsung ke containerd namespace k8s.io...${NC}"
	docker exec -i k3d-smartplatform-server-0 ctr -n k8s.io images import /tmp/smartcity-images.tar
	@echo "${GREEN}Membersihkan tarball sementara...${NC}"
	docker exec -it k3d-smartplatform-server-0 rm -f /tmp/smartcity-images.tar
	rm -f smartcity-images.tar
	@echo "${GREEN}Menyusun namespace terlebih dahulu...${NC}"
	kubectl apply -f k8s/namespace.yaml
	@echo "${GREEN}Men-deploy seluruh arsitektur ke Kubernetes...${NC}"
	kubectl apply -f k8s/
	@echo "${GREEN}Memaksa pod melakukan restart agar menggunakan image baru...${NC}"
	kubectl delete pods --all -n smartcity-energy-management-system

k8s-status:
	@echo "${GREEN}Mengecek status kluster (Namespace: smartcity-energy-management-system)...${NC}"
	kubectl get pods,svc,hpa,ingress -n smartcity-energy-management-system

k8s-down:
	@echo "${GREEN}Menghapus seluruh arsitektur dari kluster Kubernetes...${NC}"
	kubectl delete -f k8s/