# Asset Inspector SaaS - Project Architecture

## 🏗️ Three-Repository Architecture

### Repository 1: Laravel + Inertia.js Web Application
**Repository:** `asset-inspector-saas`
**Purpose:** Main web application with Laravel backend and React frontend

```
asset-inspector-saas/
├── 🏗️ Laravel Backend
│   ├── app/
│   │   ├── Http/Controllers/     # API & Web Controllers
│   │   ├── Models/              # Eloquent Models
│   │   ├── Services/            # Business Logic
│   │   └── ...
│   ├── database/
│   │   ├── migrations/          # Database Schema
│   │   └── seeders/             # Sample Data
│   ├── routes/
│   │   ├── api.php             # Mobile API Routes
│   │   └── web.php             # Web Application Routes
│   └── tests/                  # PHPUnit Tests
│
├── ⚛️ React Frontend (Inertia.js)
│   └── resources/js/
│       ├── Components/         # UI Components (shadcn/ui)
│       ├── Pages/             # Inertia Pages
│       ├── Layouts/           # Layout Components
│       └── lib/               # Utilities
│
├── 🎨 Styling
│   └── resources/css/         # Tailwind CSS
│
├── 🧪 Testing
│   ├── tests/Feature/         # Laravel Feature Tests
│   ├── tests/Unit/           # Laravel Unit Tests
│   └── tests/e2e/            # Playwright E2E Tests
│
├── 🚀 DevOps
│   ├── .github/workflows/    # GitHub Actions
│   ├── docker/               # Docker Configuration
│   ├── Dockerfile           # Application Container
│   └── docker-compose.yml   # Development Environment
│
└── 📋 Configuration
    ├── vite.config.js       # Frontend Build
    ├── tailwind.config.js   # Styling
    └── Makefile            # Development Commands
```

### Repository 2: React Native Mobile Application
**Repository:** `asset-inspector-mobile`
**Purpose:** Cross-platform mobile app with offline-first capabilities

```
asset-inspector-mobile/
├── 📱 React Native App
│   ├── src/
│   │   ├── screens/          # Mobile Screens
│   │   ├── components/       # Reusable Components
│   │   ├── services/         # API & Sync Services
│   │   ├── store/           # Redux Store & Slices
│   │   ├── utils/           # Utilities
│   │   └── types/           # TypeScript Types
│   ├── App.tsx              # Main App Component
│   └── app.json            # Expo Configuration
│
├── 🔄 Offline Capabilities
│   ├── src/services/syncService.ts    # Background Sync
│   ├── src/store/slices/offlineSlice.ts # Offline State
│   └── src/utils/networkUtils.ts       # Network Detection
│
├── 🧪 Testing
│   ├── __tests__/           # Jest Tests
│   └── e2e/                # Detox E2E Tests
│
├── 🚀 DevOps
│   ├── .github/workflows/   # GitHub Actions
│   ├── eas.json            # EAS Build Configuration
│   └── Dockerfile.dev      # Development Container
│
└── 📋 Configuration
    ├── package.json         # Dependencies
    ├── metro.config.js      # Metro Bundler
    └── babel.config.js      # Babel Configuration
```

### Repository 3: Infrastructure as Code
**Repository:** `asset-inspector-infrastructure`
**Purpose:** Complete AWS infrastructure using Terraform

```
asset-inspector-infrastructure/
├── 🏗️ Terraform Modules
│   ├── modules/
│   │   ├── networking/      # VPC, Subnets, Security Groups
│   │   ├── security/        # WAF, Secrets Manager, IAM
│   │   ├── database/        # RDS Aurora PostgreSQL
│   │   ├── cache/          # ElastiCache Redis
│   │   ├── compute/        # ECS Fargate, ALB
│   │   ├── storage/        # S3 Buckets
│   │   ├── cdn/           # CloudFront Distribution
│   │   ├── monitoring/    # CloudWatch, X-Ray
│   │   └── backup/        # AWS Backup Service
│   │
│   ├── environments/
│   │   ├── staging/       # Staging Environment
│   │   ├── production/    # Production Environment
│   │   └── dev/          # Development Environment
│   │
│   ├── main.tf           # Main Configuration
│   ├── variables.tf      # Input Variables
│   ├── outputs.tf        # Output Values
│   └── versions.tf       # Provider Versions
│
├── 🔐 Security Features
│   ├── waf/              # Web Application Firewall
│   ├── secrets/          # Secrets Manager Configuration
│   ├── iam/             # IAM Roles and Policies
│   └── kms/             # Key Management Service
│
├── 🚀 DevOps
│   ├── .github/workflows/ # GitHub Actions
│   ├── scripts/          # Deployment Scripts
│   └── docker/           # Container Configurations
│
└── 📋 Documentation
    ├── README.md         # Setup Instructions
    ├── ARCHITECTURE.md   # Infrastructure Architecture
    └── RUNBOOK.md       # Operations Guide
```

## 🔄 CI/CD Pipeline

### Main Application (Laravel + React)
1. **Push to `develop`** → Deploy to Staging
2. **Push to `main`** → Deploy to Production
3. **Pull Request** → Run tests and security scans

### Mobile Application
1. **Push to `develop`** → Build development app
2. **Push to `main`** → Build and deploy to app stores
3. **Pull Request** → Run tests and build verification

### Infrastructure
1. **Push to `develop`** → Plan and apply to staging
2. **Push to `main`** → Plan and apply to production
3. **Pull Request** → Terraform plan and security validation

## 🛡️ Security Features

### Web Application Firewall (WAF)
- AWS Managed Core Rule Set
- SQL Injection Protection
- Rate Limiting (2000 req/5min per IP)
- Geographic Blocking Capabilities
- Custom IP Blocking

### Secrets Management
- AWS Secrets Manager for sensitive data
- Automatic database password rotation
- KMS encryption for all secrets
- Parameter Store for configuration
- Environment-specific secret isolation

### Network Security
- VPC with private/public subnet separation
- Security groups with least privilege
- NACLs for additional network filtering
- WAF integration with CloudFront and ALB

### Authentication & Authorization
- Laravel Sanctum for web authentication
- JWT tokens for mobile API access
- Role-based access control (RBAC)
- Multi-tenant data isolation
- Session management with Redis

## 🚀 Deployment Strategy

### Infrastructure First
1. Deploy infrastructure using Terraform
2. Set up monitoring and alerting
3. Configure secrets and parameters

### Application Deployment
1. Zero-downtime ECS deployments
2. Database migrations in separate tasks
3. CloudFront cache invalidation
4. Health checks and rollback capabilities

### Mobile App Distribution
1. Expo Development Client for testing
2. EAS Build for production builds
3. OTA updates for non-native changes
4. App store deployment automation

## 📊 Monitoring & Observability

### Application Monitoring
- CloudWatch Logs and Metrics
- X-Ray distributed tracing
- Custom application metrics
- Performance monitoring

### Infrastructure Monitoring
- CloudWatch infrastructure metrics
- AWS Config for compliance
- CloudTrail for audit logging
- Cost monitoring and alerting

### Alerting
- Slack notifications for deployments
- Email alerts for critical issues
- PagerDuty integration for on-call
- Custom dashboards in CloudWatch

## 🔧 Development Workflow

### Local Development
```bash
# Main Application
git clone asset-inspector-saas
make setup
make dev

# Mobile Application  
git clone asset-inspector-mobile
npm install
npx expo start

# Infrastructure
git clone asset-inspector-infrastructure
cd environments/dev
terraform init
terraform plan
```

### Testing
```bash
# Backend Tests
php artisan test

# Frontend Tests
npm run test:frontend

# Mobile Tests
npm test

# Infrastructure Tests
terraform validate
terraform plan
```

This architecture provides:
- ✅ Clear separation of concerns
- ✅ Independent deployment pipelines
- ✅ Scalable infrastructure
- ✅ Comprehensive security
- ✅ Monitoring and observability
- ✅ Developer-friendly workflows