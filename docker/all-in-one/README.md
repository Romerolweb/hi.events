# Hi.Events "All-in-One" Docker Deployments

This directory provides two options for deployment depending on your infrastructure:

### 1. Orchestrated Docker Compose (Recommended)
This uses discrete, highly-scalable containers orchestrated by `docker-compose.yml`. This is the recommended approach for self-hosting on a VPS or AWS.

See the Quick Start instructions below to use this method.

### 2. Single-Container PaaS deployments (Render.com, Heroku, etc.)
Platform-as-a-Service (PaaS) providers often charge per running container. To deploy Hi.Events efficiently on these platforms, we provide a monolithic `Dockerfile.all-in-one` in the root repository.

This Dockerfile uses a multi-stage build to compile the frontend, and uses **FrankenPHP (Caddy)** to natively serve both the static Single-Page Application and the dynamic Laravel backend from a single port (`8000`), while running a background queue worker via a lightweight Supervisor process.

If deploying to Render.com, simply point your Web Service to the `Dockerfile.all-in-one` file.

---

## Quick Start with Docker Compose (Orchestrated)

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
