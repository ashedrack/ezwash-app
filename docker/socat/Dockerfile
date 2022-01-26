FROM alpine:3.8

LABEL maintainer="Victoria Etim <victoria@initsng.com>"

RUN apk --update add socat

ENTRYPOINT ["socat"]

CMD ["TCP4-LISTEN:2375,fork,reuseaddr UNIX-CONNECT:/var/run/docker.sock"]
