# Project Reorganization Plan

## 🚨 Current Issues

The current project structure has several problems:
1. Root-level mobile files (`App.js`, `src/`, `package.json`) 
2. Backend contains a `mobile-app/` folder that should be separate
3. Using outdated versions (Laravel 11, React 18)
4. Mixed frontend/mobile components

## 🎯 Target Structure (Clean & Latest Versions)

```
saas-ai/                        # Main project root
├── README.md                   # Main project documentation
├── docker-compose.yml          # Development environment
├── .env.example                # Environment template
├── .gitignore                  # Git ignore
├── Makefile                    # Build commands
│
├── backend/                    # Laravel 12 API (PHP 8.4)
│   ├── app/                   
│   ├── config/                
│   ├── database/              
│   ├── routes/                
│   ├── tests/                 
│   ├── composer.json          # Updated to Laravel 12
│   └── ...
│
├── frontend/                   # React 19 Web App (NEW LOCATION)
│   ├── src/                   
│   │   ├── components/        # shadcn/ui components
│   │   ├── pages/            # Inertia.js pages
│   │   ├── hooks/            # React 19 hooks
│   │   └── lib/              
│   ├── package.json          # React 19, Vite 6
│   ├── vite.config.js        
│   └── playwright.config.js  
│
├── mobile/                     # React Native 0.76 App (REORGANIZED)
│   ├── src/                   # Clean mobile-only code
│   │   ├── components/        
│   │   ├── screens/          
│   │   ├── navigation/       
│   │   ├── store/            # Redux Toolkit 2.0
│   │   └── services/         
│   ├── App.js                # Moved from root
│   ├── app.json              # Expo SDK 52
│   ├── package.json          # Latest React Native
│   └── eas.json              
│
├── infrastructure/             # Terraform 1.9 (AWS Provider 5.7)
│   ├── terraform/            
│   │   ├── environments/     # staging.tfvars, production.tfvars
│   │   ├── modules/          
│   │   └── main.tf           
│   └── scripts/              
│
├── docs/                       # Documentation
│   ├── api/                  
│   ├── deployment/           
│   └── architecture/         
│
└── scripts/                    # DevOps scripts
    ├── setup.sh              
    ├── deploy.sh             
    └── update-versions.sh    # Version upgrade script
```

## 🔄 Migration Steps

### 1. Update to Latest Versions

**Backend (Laravel 12)**
```bash
cd backend
composer require laravel/framework:^12.0
composer require php:^8.4
composer update
```

**Frontend (React 19)**  
```bash
mkdir frontend
cd frontend
npm init -y
npm install react@19 react-dom@19 @vitejs/plugin-react@5 vite@6
npm install @inertiajs/react@2.0 @headlessui/react@2.0
npm install tailwindcss@4.0-alpha
```

**Mobile (React Native 0.76)**
```bash
mkdir mobile  
cd mobile
npx create-expo-app@latest . --template blank
npm install @reduxjs/toolkit@2.0 react-redux@9
npm install @react-native-community/netinfo@11
```

### 2. File Reorganization

**Move root mobile files:**
```bash
# Move mobile files from root to mobile/
mv App.js mobile/
mv src/ mobile/
mv package.json mobile/  # Mobile package.json
mv package-lock.json mobile/
mv node_modules/ mobile/
```

**Extract frontend from backend:**
```bash
# Move web frontend files
mkdir frontend
mv backend/resources/js/ frontend/src/
mv backend/resources/css/ frontend/src/styles/
mv backend/package.json frontend/  # Frontend package.json
mv backend/vite.config.js frontend/
mv backend/tailwind.config.js frontend/
mv backend/playwright.config.js frontend/
```

**Clean up backend:**
```bash
cd backend
rm -rf mobile-app/  # Remove misplaced mobile folder
rm -rf node_modules/  # Remove frontend node_modules
```

### 3. Update Configurations

**Backend composer.json (Laravel 12)**
```json
{
    "require": {
        "php": "^8.4",
        "laravel/framework": "^12.0",
        "laravel/sanctum": "^4.0",
        "spatie/laravel-multitenancy": "^4.0",
        "inertiajs/inertia-laravel": "^2.0"
    }
}
```

**Frontend package.json (React 19)**
```json
{
    "dependencies": {
        "react": "^19.0.0",
        "react-dom": "^19.0.0",
        "@inertiajs/react": "^2.0.0",
        "tailwindcss": "^4.0.0-alpha.1",
        "vite": "^6.0.0"
    }
}
```

**Mobile package.json (Latest)**
```json
{
    "dependencies": {
        "react-native": "0.76.0",
        "expo": "~52.0.0",
        "@reduxjs/toolkit": "^2.0.0",
        "react-redux": "^9.0.0"
    }
}
```

## 🚀 Benefits of Clean Structure

1. **Clear Separation**: Backend API, Web Frontend, Mobile App
2. **Latest Versions**: Laravel 12, React 19, RN 0.76
3. **Independent Development**: Teams can work separately
4. **Better CI/CD**: Each part can be deployed independently
5. **Easier Maintenance**: No more mixed dependencies

## 📝 Action Items

- [ ] Create new folder structure
- [ ] Update all package versions
- [ ] Move files to correct locations  
- [ ] Update import paths
- [ ] Test each part independently
- [ ] Update documentation
- [ ] Create new docker-compose.yml
- [ ] Update CI/CD pipelines

Would you like me to proceed with this reorganization?