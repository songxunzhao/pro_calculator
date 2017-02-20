#!bin/bash
wget 
http://developer.axis.com/download/distribution/apps-sys-utils-start-stop-daemon-IR1_9_18-2.tar.gz
tar -xzf apps-sys-utils-start-stop-daemon-IR1_9_18-2.tar.gz
cd apps/sys-utils/start-stop-daemon-IR1_9_18-2/
gcc start-stop-daemon.c -o start-stop-daemon
mv start-stop-daemon /usr/bin/
cp ./spzworker /etc/init.d/
