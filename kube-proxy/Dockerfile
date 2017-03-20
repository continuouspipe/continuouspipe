FROM golang:1.7

ARG WILDCARD_SSL_CERT=
ARG WILDCARD_SSL_KEY=
ARG KUBE_PROXY_LISTEN_ADDRESS=
ARG KUBE_PROXY_INSECURE_SKIP_VERIFY=
ARG KUBE_PROXY_AUTHENTICATOR_HOST=
ARG KUBE_PROXY_RIVER_HOST=
ARG KUBE_PROXY_MASTER_API_KEY=
ARG KEEN_IO_PROJECT_ID=
ARG KEEN_IO_EVENT_COLLECTION=
ARG KEEN_IO_WRITE_KEY=

# Add the application
ADD . /go/src/github.com/continuouspipe/kube-proxy
WORKDIR /go/src/github.com/continuouspipe/kube-proxy

# Run build
RUN go install

# Run the outyet command by default when the container starts.
ENTRYPOINT ["/go/bin/kube-proxy", "-logtostderr"]