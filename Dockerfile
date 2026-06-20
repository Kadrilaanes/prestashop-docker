FROM prestashop/prestashop:latest

# Install git for webhook auto-sync
RUN apt-get update && \
    apt-get install -y --no-install-recommends git && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*
