#!/bin/bash

# Arguments

addr_local=${1};
addr_remote=${2};
root_pass=${3:-'vintage'};
user_name=${4:-'vintage'};
group_name=${5:-'vintage'};
user_id=${6:-1977};
group_id=${7:-1977};

# SSH

fp="${HOME}/.ssh/id_rsa.vintage";

if [ ! -e ${fp} ]; then
  ssh-keygen -t rsa -f ${fp} -N '' -q;
fi;

ssh_key=$(cat ${fp}.pub);

# Commands : Preparation

n="\n";
random=$(cat /dev/urandom | LC_CTYPE=C tr -cd 'a-zA-Z0-9' | fold -w 8 | head -n 1);
commands="#!/bin/bash${n}";

# Commands : Firewall

categories_n="rich-rule";    # Separated by a newline character
categories_s="port service"; # Separated by a space character
default_zone="public";

command="firewall-cmd --zone=${default_zone}";
command_p="${command} --permanent";

commands="${commands} firewall-cmd --set-default-zone=${default_zone};${n}";
commands="${commands} IFS_BACKUPED=\${IFS};${n}";
commands="${commands} for category in \$(echo ${categories_s};); do${n}";
commands="${commands}   for item in \$(${command} --list-\${category}s;); do${n}";
commands="${commands}     ${command_p} --remove-\${category}=\${item};${n}";
commands="${commands}   done;${n}";
commands="${commands} done;${n}";
commands="${commands} for category in \$(echo ${categories_n};); do${n}";
commands="${commands}   IFS=\$'\\\n';${n}";
commands="${commands}   for item in \$(${command} --list-\${category}s;); do${n}";
commands="${commands}     ${command_p} --remove-\${category}=\${item};${n}";
commands="${commands}   done;${n}";
commands="${commands} done;${n}";
commands="${commands} IFS=\${IFS_BACKUPED};${n}";
commands="${commands} ${command_p} --add-rich-rule='rule family=\"ipv4\" source address=\"${addr_local}\" port protocol=\"tcp\" port=\"60022\" accept';${n}";
commands="${commands} firewall-cmd --reload;${n}";

# Commands : SSH Daemon

fp='/etc/ssh/sshd_config';
regex='^(PasswordAuthentication|PermitRootLogin|Port) ';

commands="${commands} cp -f ${fp} ${fp}.backup.vintage.${random};${n}";
commands="${commands} egrep -v \"${regex}\" ${fp} > ${fp}.${random};${n}";
commands="${commands} echo 'PasswordAuthentication no'  >> ${fp}.${random};${n}";
commands="${commands} echo 'PermitRootLogin no'         >> ${fp}.${random};${n}";
commands="${commands} echo 'Port 60022'                 >> ${fp}.${random};${n}";
commands="${commands} mv ${fp}.${random} ${fp};${n}";

# Commands : User

commands="${commands} if [ \"\$(grep ^${user_name}: /etc/passwd;)\" != '' ]; then${n}";
commands="${commands}   userdel -r ${user_name};${n}";
commands="${commands} fi;${n}";
commands="${commands} if [ \"\$(grep ^${group_name}: /etc/group;)\" != '' ]; then${n}";
commands="${commands}   groupdel ${group_name};${n}";
commands="${commands} fi;${n}";
commands="${commands} groupadd ${group_name};${n}";
commands="${commands} useradd -g ${group_name} ${user_name};${n}";
commands="${commands} groupmod -g ${group_id} ${group_name};${n}";
commands="${commands} usermod -u ${user_id} -g ${group_name} ${user_name};${n}";
commands="${commands} mkdir -m 700 /home/${user_name}/.ssh;${n}";
commands="${commands} echo '${ssh_key}' > /home/${user_name}/.ssh/authorized_keys;${n}";
commands="${commands} chmod 600 /home/${user_name}/.ssh/authorized_keys;${n}";
commands="${commands} chown -R ${user_name}:${group_name} /home/${user_name};${n}";

# Commands : Sudoers

file_path='/etc/sudoers.d/vintage';
commands="${commands} echo '${user_name} ALL=(ALL) NOPASSWD:ALL' > ${file_path};${n}";
commands="${commands} chmod 440 ${file_path};${n}";

# Commands : Systemctl

commands="${commands} for service in \$(echo 'firewalld sshd';); do${n}";
commands="${commands}   systemctl enable  \${service}.service;${n}";
commands="${commands}   systemctl restart \${service}.service;${n}";
commands="${commands} done;${n}";

# Commands : Execution

commands="${commands} exit;";
echo -e $commands; exit;
file_name="vintage.remote.initialize.${random}.sh";
file_path="/tmp/${file_name}";
echo -e ${commands} > ${file_path};

options="-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null";
command_scp="sshpass -p ${root_pass} scp ${options} -q -r";
command_ssh="sshpass -p ${root_pass} ssh ${options} -q root@${addr_remote}";

${command_scp} ${file_path} root@${addr_remote}:/tmp;
${command_ssh} "chmod 700 ${file_path}; ${file_path}; rm -f ${file_path};";
rm -f ${file_path};

exit;
