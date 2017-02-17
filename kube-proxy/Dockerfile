FROM golang:1.7

ARG KUBE_PROXY_LISTEN_HTTPS=
ARG KUBE_PROXY_LISTEN_ADDRESS=
ARG KUBE_PROXY_INSECURE_SKIP_VERIFY=
ARG KUBE_PROXY_AUTHENTICATOR_HOST=
ARG KUBE_PROXY_MASTER_API_KEY=

# Add the application
ADD . /go/src/github.com/continuouspipe/kube-proxy
WORKDIR /go/src/github.com/continuouspipe/kube-proxy

# Install glide
RUN curl https://glide.sh/get | sh

# Install dependencies
RUN glide install --strip-vendor

# Run build
RUN go install

# Run the outyet command by default when the container starts.
ENTRYPOINT ["/go/bin/kube-proxy"]
EXPOSE 80