#!/usr/bin/env bash
# Helper script to build the docker image and run phpunit inside container
set -euo pipefail
IMAGE_NAME=querycraft-phpunit:local
WORKDIR_HOST=$(pwd)

echo "Building Docker image ${IMAGE_NAME}..."
docker build -t ${IMAGE_NAME} .

# If no arguments provided, default to running phpunit with testdox
if [ "$#" -eq 0 ]; then
  CMD=("./vendor/bin/phpunit" "--testdox" "--colors=always")
else
  CMD=("./vendor/bin/phpunit" "$@")
fi

echo "Running: ${CMD[*]}"
docker run --rm -v "${WORKDIR_HOST}":/app -w /app ${IMAGE_NAME} "${CMD[@]}"
