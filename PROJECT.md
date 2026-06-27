# Project: Smart City Integrated Platform (Smart Energy/Power Grid sub-theme)

## Architecture
- **API Gateway & OAuth 2.0 Server**: Entrypoint of requests, JWT validation, role checking, rate limiting.
- **Downstream PHP Services**:
  - `php-citizen`: Manages resident profiles and identities.
  - `php-power`: Manages power consumer/generator readings and grid usage metrics.
  - `php-grid`: Manages physical grid status, transformers, and distribution points.
- **Python ML Service**: Serves models for Power Demand, Grid Quality, and Anomaly Detection.
- **IoT Pipeline**: Integrates physical grid sensors via Wokwi and Node-RED flows.

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|---|---|---|---|
| 1 | Verify Branch and Codebase State | Check that repository is on `testing` branch, audit overall folder topology, run initial status commands. | None | DONE |
| 2 | Verify & Fix API Gateway & OAuth 2.0 | Local JWT signature verification, DB introspection, header injection, RBAC status PATCH endpoints, rate limiting, and grants. | M1 | DONE |
| 3 | Verify & Fix Downstream PHP Services | MVC structure, PDO connections, validation, RabbitMQ event publishing, health checks. | M2 | DONE |
| 4 | Verify & Fix Python ML & IoT | FastAPI schema/feature importance/batch endpoints, RabbitMQ consumers, Wokwi sketch, Node-RED flows. | M3 | DONE |
| 5 | Complete Deliverables | Populate root README.md, expand seed.sql (50 citizens, 200 grid readings), check Postman JSON and diagrams. | M4 | DONE |
| 6 | E2E Testing & Verification | Integrate all services, run Postman/E2E test suite, adversarial coverage hardening. | M5 | DONE |

## Code Layout
- `express-gateway/` — API Gateway using Express Gateway or Node.js.
- `oauth-server/` — OAuth 2.0 Authorization Server.
- `php-citizen/` — Citizen downstream microservice (PHP).
- `php-power/` or similar — Power/Grid downstream microservice (PHP).
- `php-grid/` or similar — Grid/Power downstream microservice (PHP).
- `python-ml-service/` — Python ML microservice (FastAPI).
- `iot/` — IoT/Wokwi configs and Node-RED flow files.
- `database/` — DB schemas and seed data (seed.sql).

## Interface Contracts
- To be refined during Milestone 1.
