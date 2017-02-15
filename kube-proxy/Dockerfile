FROM golang:1.7

# Add the application
ADD . /go/src/github.com/continuouspipe/kube-proxy
WORKDIR /go/src/github.com/continuouspipe/kube-proxy

# Install glide
RUN curl https://glide.sh/get | sh

# Install dependencies
RUN glide up --strip-vendor

# Run build
RUN make build