FROM golang:1.25-alpine AS base

WORKDIR /app

COPY go.mod ./

FROM base AS test

COPY . .

RUN go test ./...

FROM base AS builder

COPY . .

RUN CGO_ENABLED=0 GOOS=linux go build -o /bin/excel-worker ./cmd/excel-worker

FROM scratch

COPY --from=builder /bin/excel-worker /excel-worker

ENTRYPOINT ["/excel-worker"]
