# Hi.Events All-in-One Docker Compose Setup

This directory provides a production-ready orchestrated Docker Compose setup. It runs the backend (using FrankenPHP), frontend (Node.js SSR), queue workers, and databases using discrete, highly-scalable containers.

## Quick Start with Docker

### Step 1: Clone the Repository

```bash
git clone git@github.com:HiEventsDev/hi.events.git
cd hi.events/docker/all-in-one
```

### Step 2: Copy the Environment File

```bash
cp .env.example .env
```

### Step 3: Generate the `APP_KEY` and `JWT_SECRET`

Generate the keys using the following commands:

#### Unix/Linux/MacOS/WSL
```bash
echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env
echo "JWT_SECRET=$(openssl rand -base64 32)" >> .env
```

#### Windows (Command Prompt):
```cmd
for /f "tokens=*" %i in ('openssl rand -base64 32') do @echo APP_KEY=base64:%i >> .env
for /f "tokens=*" %i in ('openssl rand -base64 32') do @echo JWT_SECRET=%i >> .env
```

### Step 4: Start the Docker Containers

```bash
docker compose up -d
```

### Step 5: Create an Account

Visit [http://localhost:5678/auth/register](http://localhost:5678/auth/register) to view the frontend and create an account.

---

**Production Note:**  
For production, ensure you generate unique `APP_KEY` and `JWT_SECRET` for each environment and never hardcode sensitive values.
