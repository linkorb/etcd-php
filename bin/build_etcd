#!/bin/bash

wget -c https://storage.googleapis.com/golang/go1.4.linux-amd64.tar.gz
tar -zxf go1.4.linux-amd64.tar.gz
git clone https://github.com/coreos/etcd.git
export GOROOT=$PWD/go
#export GOPATH=$PWD/go
export PATH=$GOPATH/bin:$PATH
go get golang.org/x/tools/cmd/cove
go get golang.org/x/tools/cmd/vet
cd etcd && ./build

# Start etcd
./bin/etcd > /dev/null 2>&1 &
