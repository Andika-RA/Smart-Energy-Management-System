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
	@echo "${GREEN}Mengimpor image ke kluster k3d...${NC}"
	k3d image import \
		smart-city-platform-api-gateway:latest \
		smart-city-platform-php-citizen:latest \
		smart-city-platform-php-grid:latest \
		smart-city-platform-php-power:latest \
		smart-city-platform-oauth-server:latest \
		smart-city-platform-python-ml:latest \
		-c smartplatform
	@echo "${GREEN}Men-deploy arsitektur ke kluster Kubernetes...${NC}"
	kubectl apply -f k8s/

k8s-status:
	@echo "${GREEN}Mengecek status kluster (Namespace: smartcity-energy-management-system)...${NC}"
	kubectl get pods,svc,hpa,ingress -n smartcity-energy-management-system

k8s-down:
	@echo "${GREEN}Menghapus seluruh arsitektur dari kluster Kubernetes...${NC}"
	kubectl delete -f k8s/