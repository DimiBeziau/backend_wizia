@echo off
echo ==========================================
echo FIXING DOCKER STORAGE LINK
echo ==========================================

echo Running 'php artisan storage:link' inside the 'app' container...
docker compose exec app php artisan storage:link

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Failed to run command. Ensure your containers are up with:
    echo docker compose up -d
    echo.
) else (
    echo.
    echo [SUCCESS] Storage link created.
)

echo.
echo ==========================================
echo IMPORTANT CONFIGURATION CHECK
echo ==========================================
echo Please ensure your .env file (backend) contains:
echo APP_URL=http://localhost:8000
echo.
echo (Port 8000 is defined in your docker-compose.yml for the web service)
echo.
pause
