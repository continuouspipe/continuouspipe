FROM meteorhacks/meteord:base

# Install dependencies
RUN apt-get update && \
    apt-get install -y git && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy application
COPY . /app

# Copy endpoint scripts
RUN chmod +x /app/docker/run.sh /app/docker/tcp-port-scan.sh

# Run the Meteor installation
RUN bash $METEORD_DIR/on_build.sh

# Update the entrypoint
ENTRYPOINT ["/bin/bash"]
CMD ["/app/docker/run.sh"]
