# Hotel Booking App – Monorepo

A full-stack hotel booking application with user authentication, room search, booking management, and a user dashboard.

- **Backend:** Laravel (PHP) API
- **Frontend:** Vue 3 + Vite
- **Database:** MySQL
- **Deployment:** Frontend on Vercel, Backend on Render

---

## Features

- User registration and login with JWT authentication
- Room search with filters and pagination
- Real-time room availability checking
- Booking management and confirmation
- User dashboard for booking history
- Responsive design with modern UI
- API-driven architecture

---

## Project Structure

```
hotel-booking/
├── backend/              # Laravel API backend
│   ├── app/             # Application logic
│   ├── config/          # Configuration files
│   ├── database/        # Migrations, seeders, factories
│   ├── routes/          # API routes
│   └── ...
├── frontend/            # Vue 3 + Vite frontend
│   ├── src/             # Source code
│   ├── public/          # Static assets
│   └── ...
├── docker-compose.yml   # Local development setup
├── Dockerfile          # Backend deployment
└── README.md
```

---

## Local Development

### Prerequisites

- **Docker & Docker Compose** (recommended for local development)
- **Node.js 18+** and **npm** (for frontend development)
- **PHP 8.2+** and **Composer** (for backend development)
- **MySQL 8.0+** (if running without Docker)

---

### Option 1: Docker Compose (Recommended)

This will run both backend and frontend locally with MySQL database.

```bash
# Clone the repository
git clone <your-repo-url>
cd hotel-booking

# Start all services
docker-compose up --build

# Or run in background
docker-compose up -d --build
```

**Access the application:**
- **Frontend:** http://localhost:3000
- **Backend API:** http://localhost:8000/api
- **Database:** MySQL on localhost:3306

**Environment setup:**
- Backend environment is configured in `docker-compose.yml`
- Frontend automatically connects to backend at `http://localhost:8000`

---

### Option 2: Manual Setup

#### Backend Setup

```bash
cd backend

# Copy environment file
cp .env.example .env

# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Configure database in .env file
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hotel_booking
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Run migrations and seed database
php artisan migrate --seed

# Start development server
php artisan serve
```

**Backend runs at:** http://localhost:8000

#### Frontend Setup

```bash
cd frontend

# Copy environment file
cp .env.example .env

# Install dependencies
npm install

# Configure API URL in .env
echo "VITE_API_URL=http://localhost:8000" > .env

# Start development server
npm run dev
```

**Frontend runs at:** http://localhost:3000

---


## Environment Variables

### Frontend (.env)
```bash
VITE_API_URL=http://localhost:8000  # Local development
# VITE_API_URL=https://your-backend.onrender.com  # Production
```

### Backend (.env)
```bash
APP_NAME="Hotel Booking API"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=root
DB_PASSWORD=

# CORS Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost
```

---

## API Documentation

### Authentication Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `GET /api/user` - Get authenticated user

### Room Endpoints
- `GET /api/rooms` - Get available rooms with filters
- `GET /api/rooms/{id}` - Get specific room details

### Booking Endpoints
- `POST /api/bookings` - Create new booking
- `GET /api/bookings` - Get user's bookings
- `GET /api/bookings/{id}` - Get specific booking details

---

## Database Setup

### Test Account

After running migrations and seeders, you can use this test account:

```
Email:    avery@example.com
Password: password
```

or

```
email:    jacob@example.com
password: password
```


### Manual Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE hotel_booking;"

# Run migrations
php artisan migrate

# Seed with sample data
php artisan db:seed
```

---

## Development Commands

### Backend
```bash
# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Generate API documentation
php artisan route:list
```

### Frontend
```bash
# Run development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Type checking
npm run type-check
```

---

## Troubleshooting

### Common Issues

1. **CORS Errors:**
   - Ensure `FRONTEND_URL` is set correctly in backend `.env`
   - Check `backend/config/cors.php` for allowed origins

2. **Database Connection:**
   - Verify database credentials in `.env`
   - Ensure MySQL service is running

3. **Environment Variables:**
   - Frontend: Restart dev server after changing `.env`
   - Backend: Run `php artisan config:clear` after changes

### Docker Issues

```bash
# Rebuild containers
docker-compose down
docker-compose up --build

# View logs
docker-compose logs backend
docker-compose logs frontend

# Reset database
docker-compose exec backend php artisan migrate:fresh --seed
```

---

---

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

---

## Support

For support, please open an issue in the GitHub repository or contact the development team.
