# Taskio - Collaborative Board Management System

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![MariaDB](https://img.shields.io/badge/MariaDB-11.4-003545?logo=mariadb&logoColor=white)](https://mariadb.org/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)

A professional, full-featured Kanban-style board management system built with Symfony 7.3, designed for team collaboration and project management. Create boards, organize tasks into lanes, manage cards with drag-and-drop functionality, and collaborate with team members in real-time.

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation with Docker (Recommended)](#installation-with-docker-recommended)
  - [Installation with Symfony CLI](#installation-with-symfony-cli)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Testing](#testing)
- [Security Features](#security-features)
- [Contributing](#contributing)
- [License](#license)

## Features

### Core Functionality

- **Board Management**: Create, edit, and delete project boards with customizable titles
- **Lane Organization**: Structure your workflow with custom lanes (columns) that can be reordered
- **Card System**: Create and manage cards within lanes with drag-and-drop functionality
  - Card titles and detailed descriptions
  - Status tracking (Todo, In Progress, Done)
  - Position management with automatic reordering
- **Real-time Collaboration**: Share boards with team members via email invitations
- **Role-Based Access Control**:
  - **Owner**: Full control over board, lanes, cards, and collaborator management
  - **Collaborator**: View and edit permissions for shared boards
  - **Admin**: System-wide management capabilities

### User Management

- **Authentication System**:
  - Secure registration with email verification
  - Login with "Remember Me" functionality
  - Password reset via email
- **User Profiles**: Manage personal information (name, email, password)
- **Admin Dashboard**: Comprehensive user and board management interface

### Additional Features

- **Responsive Design**: Modern UI built with Tailwind CSS and DaisyUI
- **Email System**: Integrated with Mailpit for development (SymfonyMailer)
- **Rate Limiting**: Protection against spam and abuse
- **Security Measures**: CSRF protection, XSS prevention, SQL injection protection
- **Comprehensive Testing**: Unit and functional tests with PHPUnit

## Technology Stack

### Backend
- **Framework**: Symfony 7.3
- **PHP Version**: 8.2+
- **Database**: MariaDB 11.4
- **ORM**: Doctrine ORM 3.5
- **Authentication**: Symfony Security Bundle
- **Email**: Symfony Mailer + Mailpit (development)

### Frontend
- **Build Tool**: Vite 6.3
- **CSS Framework**: Tailwind CSS 3.4 + DaisyUI 4.12
- **JavaScript**: Stimulus (Hotwired)
- **Turbo**: Hotwire Turbo for enhanced navigation
- **Drag & Drop**: SortableJS via Stimulus Components

### DevOps
- **Containerization**: Docker + Docker Compose
- **Web Server**: Apache (in Docker)
- **Testing**: PHPUnit 11.5
- **Fixtures**: Doctrine Fixtures + Foundry

### Key Design Patterns

- **Voter Pattern**: Fine-grained authorization control (BoardVoter)
- **Repository Pattern**: Clean data access layer
- **Service Layer**: Business logic separation (BoardInvitationService)
- **Form Components**: Type-safe form handling
- **Stimulus Controllers**: Organized client-side interactions

## Getting Started

### Prerequisites

#### For Docker Installation (Recommended)
- Docker Engine 20.10+
- Docker Compose 2.0+

#### For Symfony CLI Installation
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and npm
- MariaDB 11.4 or MySQL 8.0+
- Symfony CLI (optional but recommended)

### Installation with Docker (Recommended)

Docker provides the easiest way to run the application with all dependencies pre-configured.

1. **Clone the repository**
   ```bash
   git clone https://github.com/Baylox/taskio.git
   cd taskio
   ```

2. **Configure environment variables**
   ```bash
   cp .env .env.local
   ```

   Edit `.env.local` and update the following if needed:
   ```env
   # Database (already configured for Docker)
   DATABASE_URL="mysql://app:!ChangeMe!@database:3306/app"

   # Mailer (Mailpit for development)
   MAILER_DSN=smtp://mailer:1025
   ```

3. **Build and start Docker containers**
   ```bash
   docker compose up -d --build
   ```

   This will start the following services:
   - **app**: Symfony application (http://localhost:8080)
   - **database**: MariaDB database server
   - **vite**: Vite dev server with hot reload (http://localhost:3000)
   - **mailer**: Mailpit for email testing (http://localhost:8025)

4. **Install dependencies and set up the database**
   ```bash
   # Access the app container
   docker compose exec app bash

   # Inside the container:
   composer install
   php bin/console doctrine:migrations:migrate --no-interaction

   # Load sample data (optional)
   php bin/console doctrine:fixtures:load --no-interaction
   ```

5. **Access the application**
   - Application: http://localhost:8080
   - Mailpit (Email testing): http://localhost:8025

6. **Default credentials (if fixtures were loaded)**
   - Admin: `admin@example.com` / `password`
   - User: `user@example.com` / `password`

### Installation with Symfony CLI

For local development without Docker:

1. **Clone the repository**
   ```bash
   git clone https://github.com/Baylox/taskio.git
   cd taskio
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env .env.local
   ```

   Update `.env.local` with your local database credentials:
   ```env
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/taskio"
   MAILER_DSN=smtp://localhost:1025
   ```

5. **Create and configure the database**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate

   # Load sample data (optional)
   php bin/console doctrine:fixtures:load
   ```

6. **Start the development servers**

   In separate terminal windows:

   ```bash
   # Terminal 1: Symfony server
   symfony server:start

   # Terminal 2: Vite dev server
   npm run dev

   # Terminal 3: Mailpit (optional, for email testing)
   mailpit
   ```

7. **Access the application**
   - Application: http://localhost:8000 (or the port shown by Symfony)
   - Mailpit: http://localhost:8025

## Usage

### Creating Your First Board

1. **Register an account** or log in
2. Navigate to **"My Boards"**
3. Click **"Create Board"**
4. Enter a board title and save
5. Add lanes (columns) to organize your workflow
6. Create cards within lanes to track tasks

### Collaborating with Team Members

1. Open a board you own
2. Click **"Edit Board"** or go to board settings
3. In the **"Collaborators"** section, enter a team member's email
4. Send the invitation
5. The recipient will receive an email with an invitation link
6. Once accepted, they can view and edit the board

### Managing Cards

- **Create**: Click "Add Card" in any lane
- **Edit**: Click on a card to view/edit details
- **Move**: Drag and drop cards between lanes or reorder within a lane
- **Delete**: Use the delete button on the card detail page

### Admin Features

Administrators can:
- View all boards and users in the system
- Search and filter boards by title or owner
- Delete any board regardless of ownership
- Manage user accounts and permissions

## Project Structure

```
taskio/
├── assets/                 # Frontend assets (JS, CSS)
│   ├── controllers/        # Stimulus controllers
│   └── styles/            # Tailwind CSS
├── config/                # Symfony configuration
│   ├── packages/          # Bundle configurations
│   └── routes/            # Routing definitions
├── docker/                # Docker configuration files
│   ├── apache.conf        # Apache virtual host
│   └── vite/              # Vite container config
├── migrations/            # Database migrations
├── public/                # Web root
│   ├── build/            # Built assets
│   └── index.php         # Entry point
├── src/
│   ├── Controller/        # HTTP controllers
│   │   └── Admin/        # Admin controllers
│   ├── Entity/           # Doctrine entities
│   ├── Form/             # Form types
│   ├── Repository/       # Data repositories
│   ├── Security/         # Security (Voters, etc.)
│   ├── Service/          # Business logic services
│   └── Story/            # Foundry stories
├── templates/            # Twig templates
├── tests/                # PHPUnit tests
│   ├── Unit/            # Unit tests
│   └── Functional/      # Functional tests
├── compose.yaml          # Docker Compose config
├── Dockerfile            # Production Docker image
└── README.md            # This file
```

## Testing

The application includes comprehensive test coverage:

### Running All Tests

```bash
# With Docker
docker compose exec app php bin/phpunit

# With Symfony CLI
php bin/phpunit
```

### Running Specific Test Suites

```bash
# Unit tests only
php bin/phpunit tests/Unit

# Functional tests only
php bin/phpunit tests/Functional

# Specific test class
php bin/phpunit tests/Unit/Security/Voter/BoardVoterTest.php

# Specific test method
php bin/phpunit --filter testAdminCanDoEverything
```

### Test Configuration

Test configuration is defined in `phpunit.xml`:
- Separate test database
- Bootstrap file for test environment
- Coverage reports (when Xdebug is enabled)

### Setting Up Test Database

Before running tests, you need to create and configure the test database:

```bash
# With Docker
docker compose exec app bash
# Then inside container:
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# With Symfony CLI (local)
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

You can also load test data using Foundry factories:
```bash
php bin/console foundry:load-fixtures --env=test
```

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`php bin/phpunit`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Code Standards
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Keep commits atomic and well-described


**You are free to:**
- Use commercially
- Modify
- Distribute
- Use privately
- Sublicense

**Under the condition:**
- Include the original copyright notice and license in any copy of the software/source
---
### **Built with** by the Taskio Team

For questions or support, please open an issue on [GitHub](https://github.com/Baylox/taskio/issues).


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

