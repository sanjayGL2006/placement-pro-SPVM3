# Containerization with Docker and Deployment to Vercel

Containerize the Flask web application using Docker to allow running it consistently in any environment, and configure the project for seamless hosting on Vercel.

## User Review Required

> [!NOTE]
> Since the `docker` CLI is not currently available or active in the PATH of the current environment, you will be able to build and run the Docker image locally on your own machine (where Docker Desktop or the Docker daemon is installed) using the commands provided in the Verification Plan.

> [!IMPORTANT]
> The Vercel configuration routes all dynamic routes to `api/index.py` (which runs your Flask application), while Vercel's CDN will automatically serve static files (like `index.html`, CSS, and JS) directly for maximum speed and performance.

## Open Questions

There are no open questions. The implementation follows standard containerization and Vercel routing practices.

## Proposed Changes

### Configuration files

#### [NEW] [Dockerfile](file:///c:/Users/Sanjay%20G%20L/Desktop/portfolio/Dockerfile)
Create a Dockerfile to build a lightweight container for the Flask application using Python 3.11-slim and Gunicorn.

#### [NEW] [.dockerignore](file:///c:/Users/Sanjay%20G%20L/Desktop/portfolio/.dockerignore)
Exclude unnecessary files (like git metadata, pycache, local environments, etc.) from being copied into the Docker image to optimize size.

#### [NEW] [vercel.json](file:///c:/Users/Sanjay%20G%20L/Desktop/portfolio/vercel.json)
Configure Vercel builds to use the `@vercel/python` builder for the entrypoint and route requests to the Flask server.

#### [NEW] [api/index.py](file:///c:/Users/Sanjay%20G%20L/Desktop/portfolio/api/index.py)
Create the entrypoint directory and file that Vercel requires. This file dynamically adjusts the system path to import and run your root `app.py`.

---

## Verification Plan

### Automated Tests
* None.

### Manual Verification
1. **Docker Verification (on machine with Docker installed):**
   * Build the Docker image:
     ```bash
     docker build -t portfolio-app .
     ```
   * Run the container:
     ```bash
     docker run -d -p 5000:5000 --name portfolio-container portfolio-app
     ```
   * Access the application at `http://localhost:5000` to verify it works as expected.
2. **Vercel Verification:**
   * Run Vercel CLI locally (if installed) to test the serverless function:
     ```bash
     vercel dev
     ```
   * Push the repository to GitHub and connect it to Vercel, or run `vercel --prod` to deploy.
