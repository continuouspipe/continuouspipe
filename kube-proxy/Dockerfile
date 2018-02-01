FROM golang:1.9

# Add the application
ADD . /go/src/github.com/continuouspipe/kube-proxy
WORKDIR /go/src/github.com/continuouspipe/kube-proxy

# Run build
RUN go install

# Add the bootstap
COPY ./docker/run /usr/bin/kube-proxy
CMD ["/usr/bin/kube-proxy"]
