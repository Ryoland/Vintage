package Vintage::C::Util::Packager::Yum; {

    use strict;
    use warnings;
    use utf8;

    sub run {

        my $a     = shift;
        my $confs = $a->{confs};

        my %packages = (
            install         => {},
            installed       => {},
            not_installed   => {},
            uninstall       => {},
            uninstalled     => {},
            not_uninstalled => {}
        );

        my $rpms       = rpms();
        my $conf       = {};
        my $name       = '';
        my $package    = {};
        my $packages   = [];
        my $real       = '';
        my $repository = '';
        my $res        = {};

        for $conf (@$confs) {

            if ($conf->{install}) {
                while (($repository, $packages) = each(%{$conf->{install}})) {
                    for $package (@$packages) {

                        if (ref($package)) {
                            $name = $package->{name};
                            $real = $package->{real} || $name;
                        } else {
                            $name = $package;
                            $real = $package;
                        }

                        unless (is_installed($real, $rpms)) {
                            $packages{install}->{$repository}        ||= {};
                            $packages{install}->{$repository}->{$name} = $real;
                        }
                    }
                }
            }

            if ($conf->{uninstall}) {
                for $packages (@{$conf->{uninstall}}) {
                    for $package (@$packages) {

                        if (ref($package)) {
                            $name = $package->{name};
                            $real = $package->{real};
                        } else {
                            $name = $package;
                            $real = $package;
                        }

                        if (is_installed($real, $rpms)) {
                            $packages{uninstall}->{$name} = $real;
                        }
                    }
                }
            }
        }

        while (($repository, $packages) = each(%{$packages{install}})) {

            $res = install({
                repository => $repository,
                packages   => $packages
            });
        }

        $res = uninstall({packages => $packages{uninstall}});

        return \%packages;
    }

    sub install {

        my $a          = shift            || {};
        my $repository = $a->{repository} || 'default';
        my $packages   = $a->{packages}   || {};

        my $r = {
                installed => {},
            not_installed => {}
        };

        if (%$packages) {

            my $command  = 'env yum -y install';
            my $options  = ($repository eq 'default') ?
                ' ' : " --enablerepo=$repository ";
            my @packages = values(%$packages);

            system($command . $options . join(' ', @packages));

            my $rpms = rpms();
            my $name = '';
            my $real = '';

            while (($name, $real) = each(%$packages)) {
                if (is_installed($real, $rpms)) {
                    $r->{    installed}->{$name} = $real;
                } else {
                    $r->{not_installed}->{$name} = $real;
                }
            }
        }

        return $r;
    }

    sub uninstall {

        my $a        = shift          || {};
        my $packages = $a->{packages} || {};

        my $r = {
                uninstalled => {},
            not_uninstalled => {}
        };

        if (%$packages) {

            my $command  = 'env yum -y erase';
            my @packages = values(%$packages);

            system($command . ' ' . join(' ', @packages));

            my $rpms = rpms();
            my $name = '';
            my $real = '';

            while (($name, $real) = each(%$packages)) {
                if (!is_installed($real, $rpms)) {
                    $r->{    uninstalled}->{$name} = $real;
                } else {
                    $r->{not_uninstalled}->{$name} = $real;
                }
            }
        }

        return $r;
    }

    sub is_installed {
        my $package = shift || return undef;
        my $rpms    = shift || rpms();
        my $regex   = sprintf('^%s-\d', $package);
        return grep(/$regex/, @$rpms) ? 1 : 0;
    }

    sub rpms {
        my @rpms = qx{env rpm -aq};
        chomp(@rpms);
        return \@rpms;
    }
}

1;
