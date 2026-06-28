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
	@echo "${GREEN}Membangun image langsung di dalam Minikube...${NC}"
	@eval $$(minikube docker-env) && docker compose build
	@echo "${GREEN}Men-deploy arsitektur ke kluster Kubernetes...${NC}"
	kubectl apply -f k8s/

k8s-status:
	@echo "${GREEN}Mengecek status kluster (Namespace: smartcity-platform)...${NC}"
	kubectl get pods,svc,hpa,ingress -n smartcity-platform

k8s-down:
	@echo "${GREEN}Menghapus seluruh arsitektur dari kluster Kubernetes...${NC}"
	kubectl delete -f k8s/