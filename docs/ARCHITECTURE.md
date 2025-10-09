# Architecture Documentation

This document provides a technical overview of Taskio's architecture, project structure, and design patterns.

## Table of Contents

- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Design Patterns](#design-patterns)
- [Database Schema](#database-schema)
- [Frontend Architecture](#frontend-architecture)
- [Security Architecture](#security-architecture)
- [API Design](#api-design)

## Technology Stack

### Backend

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| Framework | Symfony | 7.3 | PHP web application framework |
| PHP | PHP | 8.2+ | Server-side language |
| Database | MariaDB | 11.4 | Relational database |
| ORM | Doctrine ORM | 3.5 | Database abstraction layer |
| Authentication | Symfony Security | - | User authentication & authorization |
| Email | Symfony Mailer | - | Email delivery system |
| Testing | PHPUnit | 11.5 | Unit and functional testing |
| Fixtures | Foundry | - | Test data generation |

### Frontend

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| Build Tool | Vite | 6.3 | Fast development and bundling |
| CSS Framework | Tailwind CSS | 3.4 | Utility-first CSS framework |
| UI Components | DaisyUI | 4.12 | Component library for Tailwind |
| JavaScript | Stimulus | - | Modest JavaScript framework |
| Navigation | Turbo | - | SPA-like page navigation |
| Drag & Drop | SortableJS | - | Drag and drop functionality |

### DevOps

| Component | Technology | Purpose |
|-----------|-----------|---------|
| Containerization | Docker + Compose | Development environment |
| Web Server | Apache | HTTP server (in Docker) |
| Mail Testing | Mailpit | Development email testing |

## Project Structure

```
taskio/
├── assets/                     # Frontend assets
│   ├── controllers/            # Stimulus controllers
│   │   ├── hello_controller.js
│   │   └── sortable_controller.js
│   ├── styles/                 # CSS files
│   │   └── app.css            # Tailwind imports
│   ├── app.js                 # Main JavaScript entry
│   └── bootstrap.js           # Stimulus bootstrap
│
├── config/                     # Symfony configuration
│   ├── packages/               # Bundle configurations
│   │   ├── doctrine.yaml
│   │   ├── security.yaml
│   │   ├── twig.yaml
│   │   └── ...
│   ├── routes/                 # Routing definitions
│   │   └── routes.yaml
│   └── services.yaml           # Service container config
│
├── docker/                     # Docker configuration
│   ├── apache.conf             # Apache virtual host
│   └── vite/                   # Vite container setup
│
├── migrations/                 # Database migrations
│   └── VersionYYYYMMDDHHMMSS.php
│
├── public/                     # Web root
│   ├── build/                  # Compiled assets
│   └── index.php               # Application entry point
│
├── src/                        # Application source code
│   ├── Controller/             # HTTP controllers
│   │   ├── Admin/              # Admin-specific controllers
│   │   │   └── AdminController.php
│   │   ├── BoardController.php
│   │   ├── CardController.php
│   │   ├── LaneController.php
│   │   └── SecurityController.php
│   │
│   ├── Entity/                 # Doctrine entities
│   │   ├── Board.php
│   │   ├── Card.php
│   │   ├── Lane.php
│   │   └── User.php
│   │
│   ├── Form/                   # Form types
│   │   ├── BoardType.php
│   │   ├── CardType.php
│   │   └── RegistrationType.php
│   │
│   ├── Repository/             # Data repositories
│   │   ├── BoardRepository.php
│   │   ├── CardRepository.php
│   │   ├── LaneRepository.php
│   │   └── UserRepository.php
│   │
│   ├── Security/               # Security components
│   │   └── Voter/
│   │       └── BoardVoter.php
│   │
│   ├── Service/                # Business logic services
│   │   └── BoardInvitationService.php
│   │
│   ├── DataFixtures/           # Database fixtures
│   │   └── AppFixtures.php
│   │
│   ├── Factory/                # Foundry factories
│   │   ├── BoardFactory.php
│   │   ├── UserFactory.php
│   │   └── ...
│   │
│   └── Kernel.php              # Application kernel
│
├── templates/                  # Twig templates
│   ├── admin/                  # Admin templates
│   ├── board/                  # Board templates
│   ├── card/                   # Card templates
│   ├── security/               # Auth templates
│   └── base.html.twig          # Base layout
│
├── tests/                      # Test suite
│   ├── Unit/                   # Unit tests
│   │   ├── Security/
│   │   │   └── Voter/
│   │   └── Service/
│   └── Functional/             # Functional tests
│       └── Controller/
│
├── var/                        # Generated files
│   ├── cache/                  # Application cache
│   └── log/                    # Application logs
│
├── vendor/                     # Composer dependencies
│
├── .env                        # Environment variables template
├── .env.local                  # Local environment overrides
├── compose.yaml                # Docker Compose configuration
├── composer.json               # PHP dependencies
├── Dockerfile                  # Production Docker image
├── package.json                # Node.js dependencies
├── phpunit.xml                 # PHPUnit configuration
├── symfony.lock                # Symfony Flex lock file
├── tailwind.config.js          # Tailwind configuration
└── vite.config.js              # Vite configuration
```

## Design Patterns

### Repository Pattern

**Purpose**: Separate data access logic from business logic.

**Implementation**:
```php
// src/Repository/BoardRepository.php
class BoardRepository extends ServiceEntityRepository
{
    public function findByOwnerOrCollaborator(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.collaborators', 'c')
            ->where('b.owner = :user OR c.id = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
```

**Benefits**:
- Clean separation of concerns
- Reusable query logic
- Easy to test and mock

### Voter Pattern

**Purpose**: Fine-grained authorization control.

**Implementation**:
```php
// src/Security/Voter/BoardVoter.php
class BoardVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['view', 'edit', 'delete'])
            && $subject instanceof Board;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Admins can do everything
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Owner can do everything
        if ($subject->getOwner() === $user) {
            return true;
        }

        // Collaborators can view and edit, but not delete
        if ($subject->getCollaborators()->contains($user)) {
            return $attribute !== 'delete';
        }

        return false;
    }
}
```

**Benefits**:
- Centralized authorization logic
- Easy to extend and maintain
- Reusable across controllers

### Service Layer Pattern

**Purpose**: Encapsulate complex business logic.

**Implementation**:
```php
// src/Service/BoardInvitationService.php
class BoardInvitationService
{
    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository
    ) {}

    public function sendInvitation(Board $board, string $email): void
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        // Add collaborator
        $board->addCollaborator($user);

        // Send email
        $this->sendInvitationEmail($board, $user);
    }
}
```

**Benefits**:
- Business logic separate from controllers
- Easy to test
- Reusable across the application

### Form Type Pattern

**Purpose**: Encapsulate form logic and validation.

**Implementation**:
```php
// src/Form/BoardType.php
class BoardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Board Title',
                'attr' => ['class' => 'input input-bordered'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Board::class,
        ]);
    }
}
```

**Benefits**:
- Reusable form definitions
- Type-safe form handling
- Centralized validation rules

## Database Schema

### Entity Relationships

```
User
  ├── owns many Boards (one-to-many)
  └── collaborates on many Boards (many-to-many)

Board
  ├── owned by one User (many-to-one)
  ├── has many Collaborators (many-to-many with User)
  └── has many Lanes (one-to-many)

Lane
  ├── belongs to one Board (many-to-one)
  └── has many Cards (one-to-many)

Card
  ├── belongs to one Lane (many-to-one)
  └── has status (enum: Todo, In Progress, Done)
```

### Key Entities

**User Entity:**
- `id` (primary key)
- `email` (unique)
- `password` (hashed)
- `name`
- `roles` (JSON array)
- Relationships: ownedBoards, collaboratedBoards

**Board Entity:**
- `id` (primary key)
- `title`
- `owner` (User foreign key)
- `createdAt`, `updatedAt`
- Relationships: owner, collaborators, lanes

**Lane Entity:**
- `id` (primary key)
- `title`
- `position` (integer for ordering)
- `board` (Board foreign key)
- Relationships: board, cards

**Card Entity:**
- `id` (primary key)
- `title`
- `description`
- `status` (enum)
- `position` (integer for ordering)
- `lane` (Lane foreign key)
- Relationships: lane

## Frontend Architecture

### Stimulus Controllers

Taskio uses **Stimulus** for JavaScript interactions.

**Sortable Controller** (Drag & Drop):
```javascript
// assets/controllers/sortable_controller.js
import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    connect() {
        this.sortable = Sortable.create(this.element, {
            animation: 150,
            onEnd: this.onEnd.bind(this),
        });
    }

    async onEnd(event) {
        // Update positions via AJAX
        const url = event.item.dataset.updateUrl;
        await fetch(url, {
            method: 'PATCH',
            body: JSON.stringify({ position: event.newIndex }),
        });
    }
}
```

### Tailwind + DaisyUI

**Styling Approach**:
- **Tailwind CSS**: Utility-first CSS for custom designs
- **DaisyUI**: Pre-built components (buttons, cards, modals)
- **Responsive**: Mobile-first design

**Example**:
```html
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title">Board Title</h2>
        <p>Description here</p>
        <div class="card-actions justify-end">
            <button class="btn btn-primary">Open</button>
        </div>
    </div>
</div>
```

### Turbo (Hotwire)

**Purpose**: SPA-like navigation without full page reloads.

**Features**:
- Automatic page caching
- Form submissions via AJAX
- Progress bar during navigation

## Security Architecture

### Authentication

- **Symfony Security Bundle**: Handles user authentication
- **Password Hashing**: Uses Argon2i algorithm
- **Remember Me**: Persistent login tokens
- **Email Verification**: Confirms user email addresses

### Authorization

- **Role-Based Access Control (RBAC)**:
  - `ROLE_USER`: Standard user
  - `ROLE_ADMIN`: Administrator
- **Voter-Based Permissions**: Fine-grained access control per resource

### Security Measures

1. **CSRF Protection**: Built into Symfony forms
2. **XSS Prevention**: Twig auto-escapes output
3. **SQL Injection Protection**: Doctrine parameterized queries
4. **Rate Limiting**: Prevents spam and abuse
5. **Secure Password Reset**: Time-limited tokens

## API Design

### RESTful Principles

Taskio follows RESTful conventions for resource management:

| HTTP Method | Route | Action | Description |
|------------|-------|--------|-------------|
| GET | `/board` | index | List all boards |
| GET | `/board/new` | new | Show creation form |
| POST | `/board` | create | Create new board |
| GET | `/board/{id}` | show | Display board |
| GET | `/board/{id}/edit` | edit | Show edit form |
| PUT/PATCH | `/board/{id}` | update | Update board |
| DELETE | `/board/{id}` | delete | Delete board |

### AJAX Endpoints

For dynamic interactions (drag & drop, etc.):

```php
#[Route('/card/{id}/position', methods: ['PATCH'])]
public function updatePosition(Card $card, Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $card->setPosition($data['position']);
    $this->entityManager->flush();

    return new JsonResponse(['success' => true]);
}
```

## Performance Considerations

1. **Database Indexing**: Foreign keys and frequently queried fields
2. **Lazy Loading**: Doctrine loads related entities on demand
3. **Asset Optimization**: Vite bundles and minifies assets
4. **Caching**: Symfony HTTP cache and OPcache

## Extensibility

### Adding New Features

1. **Create Entity**: Define the data model
2. **Create Repository**: Add custom queries if needed
3. **Create Controller**: Handle HTTP requests
4. **Create Form Type**: Define form structure
5. **Create Templates**: Build the UI
6. **Add Routes**: Register URL patterns
7. **Write Tests**: Ensure functionality works

### Configuration

- **Environment Variables**: Configure via `.env` files
- **Bundle Configuration**: Modify `config/packages/` files
- **Services**: Register services in `config/services.yaml`

## Additional Resources

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Stimulus Handbook](https://stimulus.hotwired.dev/)
- [Tailwind CSS](https://tailwindcss.com/docs)

---

[← Back to README](../README.md) | [Testing Guide](TESTING.md) | [Contributing →](../CONTRIBUTING.md)
