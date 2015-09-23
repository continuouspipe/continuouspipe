FROM busybox
RUN echo "foo" /foo
ADD . /app
RUN chmod +x /app/private-check.sh
RUN /app/private-check.sh
RUN echo "bar" /app/bar
WORKDIR /app
#~continuous-pipe:inject-environment~#
RUN ./private-check.sh
