# Placement Pro — Backend Dockerfile
FROM python:3.12-slim

# Prevent Python from writing .pyc files & enable unbuffered logging
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

# Set default data storage paths for persistent volumes
ENV DATABASE_PATH=/app/data/placement_pro.db
ENV UPLOAD_FOLDER=/app/data/uploads

WORKDIR /app

# Install system dependencies needed for lxml and document processing
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    gcc \
    libffi-dev \
    libxml2-dev \
    libxslt-dev \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Python requirements
COPY requirements.txt /app/
RUN pip install --no-cache-dir -r requirements.txt

# Copy application backend source
COPY . /app/

# Create data directory for volume mounts
RUN mkdir -p /app/data /app/data/uploads

EXPOSE 5500

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -f http://localhost:5500/api/health || exit 1

CMD ["gunicorn", "--bind", "0.0.0.0:5500", "--workers", "2", "--threads", "4", "app:create_app()"]
