FROM busybox
RUN echo "foo" /foo
ADD . /app
RUN chmod +x private-check.sh
RUN ./private-check.sh
RUN echo "bar" /app/bar
#~continuous-pipe:inject-environment~#
WORKDIR /app
RUN ./private-check.sh
