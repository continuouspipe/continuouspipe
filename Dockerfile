FROM gcr.io/cloud-builders/docker

COPY ./bin/cloud-builder-linux-amd64 /usr/bin/cloud-builder

ENTRYPOINT ["/usr/bin/cloud-builder"]
