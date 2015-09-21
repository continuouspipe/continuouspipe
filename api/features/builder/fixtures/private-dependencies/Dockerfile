FROM busybox
RUN echo "foo" /foo
ADD . /app
RUN chmod +x private-check.sh
RUN ./private-check.sh
RUN echo "bar" /app/bar
WORKDIR /app
#~continuous-pipe:inject-environment~#
RUN ./private-check.sh
