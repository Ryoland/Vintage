package Vintage::C::Util::Arrange; {

    ##========================================================================
    ## Use
    ##========================================================================

    use strict;
    use warnings;
    use utf8;

    use File::Copy;
    use File::Copy::Recursive;
    use File::Path;
    use YAML::Syck;
    use Vintage::C::Etc;
    use Vintage::C::Util::Env;

    ##========================================================================
    ## Our
    ##========================================================================

    our $EXTENSION = 'yml';

    ##========================================================================
    ## Sub
    ##========================================================================

    sub run {

        my $a         = shift;
        my $project   = $a->{project}
                     || Vintage::C::Util::Env::manager();
        my $extension = $a->{extension}
                     || $Vintage::C::Util::Arrange::EXTENSION;

        my $fps_conf = Vintage::C::Etc::fps_conf({
            project   => $project,
            program   => 'manage/arrange',
            extension => $extension,
            if_exists => 1
        });

        my $area = Vintage::C::Util::Env::area();
        my $host = Vintage::C::Util::Env::host();

        my ($data, $confs, $conf);
        my ($path, $dest, $user, $group, $mode);
        my ($np_from, $np_dest, $dp_dest, $uid, $gid, $restart);

        foreach my $fp_conf (@$fps_conf) {

            $data = YAML::Syck::LoadFile($fp_conf);

            if ($confs = $data->{conf}) {
                foreach $conf (@$confs) {

                    if (ref $conf) {
                        $path  = $conf->{path}  || next;
                        $dest  = $conf->{dest}  || $path;
                        $user  = $conf->{user}  || 'root';
                        $group = $conf->{group} || 'root';
                        $mode  = $conf->{mode}  || undef;
                    } else {
                        $path  = $conf || next;
                        $dest  = $path;
                        $user  = 'root';
                        $group = 'root';
                        $mode  = undef;
                    }

                    $np_from = sprintf(
                        '%s/pro/%s/Vintage/dat/manage/arrange/%s',
                        $ENV{VTG_ROOT},
                        $project,
                        $path
                    );

                    $np_from =~ s/\$area/$area/g;
                    $np_from =~ s/\$host/$host/g;
                    $np_from =~ s/\/\//\//g;
                    $np_dest =  $dest;
                    $dp_dest =  $np_dest;
                    $dp_dest =~ s/\/[^\/]+$/\//g;
                    $uid     =  getpwnam $user;
                    $gid     =  getgrnam $group;
                    $restart =  0;

                    unless (-e $dp_dest) {
                        File::Path::mkpath($dp_dest);
                        chown($uid, $gid, $dp_dest);
                    }

                    if (-e $np_from) {
                        if (-d $np_from) {
                            File::Path::rmtree($np_dest);
                            File::Copy::Recursive::rcopy($np_from, $np_dest);
                        } elsif (-f $np_from) {
                            File::Copy::copy($np_from, $np_dest);
                        } else { next; }
                    }

                    chown($uid, $gid, $np_dest);
                    chmod(oct($mode), $np_dest) if $mode;
                }
            }
        }
    }

    ##========================================================================
}

1;
