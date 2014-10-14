#!/bin/bash

##########
## 引数 ##
##########

ARGUMENTS=;

##########
## 読込 ##
##########

source $VTG_ROOT/pro/Vintage/lib/sh/initialize;

##########
## 関数 ##
##########

proc() {
    init;
    save;
}

init() {

    $CP_IPTABLES -F;
    $CP_IPTABLES -X;

    $CP_IPTABLES -P INPUT   DROP;
    $CP_IPTABLES -P OUTPUT  DROP;
    $CP_IPTABLES -P FORWARD DROP;

    $CP_IPTABLES -A INPUT  -i lo -j ACCEPT;
    $CP_IPTABLES -A OUTPUT -o lo -j ACCEPT;

    $CP_IPTABLES -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT;

    $CP_IPTABLES -A INPUT  -p tcp -s 0.0.0.0/0 --dport  22 -j ACCEPT;
    $CP_IPTABLES -A OUTPUT -p tcp -d 0.0.0.0/0 --sport  22 -j ACCEPT;

    $CP_IPTABLES -A OUTPUT -p udp -d 0.0.0.0/0 --dport  53 -j ACCEPT;
    $CP_IPTABLES -A INPUT  -p udp -s 0.0.0.0/0 --sport  53 -j ACCEPT;

    $CP_IPTABLES -A OUTPUT -p tcp -d 0.0.0.0/0 --dport  21 -j ACCEPT;
    $CP_IPTABLES -A OUTPUT -p tcp -d 0.0.0.0/0 --dport  22 -j ACCEPT;
    $CP_IPTABLES -A OUTPUT -p tcp -d 0.0.0.0/0 --dport  80 -j ACCEPT;
    $CP_IPTABLES -A OUTPUT -p tcp -d 0.0.0.0/0 --dport 443 -j ACCEPT;
}

save() {
    $CP_IPTABLES_SAVE -c > /etc/sysconfig/iptables;
    $CP_CHKCONFIG --level 3 iptables on;
}

##########
## 処理 ##
##########

proc;

exit 0;
