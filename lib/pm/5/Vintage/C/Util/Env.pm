package Vintage::C::Util::Env; {

    use strict;
    use warnings;
    use utf8;
    use YAML::Syck;

    our $ETC = {};

    sub configure {

        my $etc = {
            roles   => { global => 0 },
            servers => {},
            clients => {},
            apps    => {},
            tags    => {}
        };

        my $area    = area()    || return undef;
        my $host    = host()    || return undef;
        my $manager = manager() || return undef;

        my $dp  = $ENV{VTG_ROOT};
           $dp .= "/pro/$manager/Vintage/etc";

        my ($fd, $fp, @fps, $k1, $k2);

        if (-f ($fp = "$dp/hosts/$area.yml")) {
            if ($fd = YAML::Syck::LoadFile($fp)) {
                if ($fd->{conf} && $fd->{conf}->{$host}) {
                    for $k1 (keys %$etc) {
                        map { $etc->{$k1}->{$_} = 0; }
                            @{$fd->{conf}->{$host}->{$k1} || []};
                    }
                }
            }
        }

        my $done = 0;

        do {

            for $k1 (keys %$etc) {

                @fps = ();

                for $k2 (keys %{$etc->{$k1}}) {
                    next if $etc->{$k1}->{$k2};
                    push @fps, "$dp/$k1/$k2.yml";
                    $etc->{$k1}->{$k2} = 1;
                }

                for $fp (@fps) {
                    if ((-f $fp) && ($fd = YAML::Syck::LoadFile($fp))) {
                        if ($fd->{conf}) {
                            for $k1 (keys %$etc) {
                                map { $etc->{$k1}->{$_} ||= 0; }
                                    @{$fd->{conf}->{$k1} || []};
                            }
                        }
                    }
                }
            }

            $done = 1;

            for $k1 (keys %$etc) {
                for $k2 (keys %{$etc->{$k1}}) {
                    $done *= $etc->{$k1}->{$k2};
                }
            }

        } while (!$done);

        while (my ($name, $list) = each(%$etc)) {
            $fp = $ENV{VTG_ROOT} . "/etc/$name";
            open  FH, "> $fp";
            print FH join("\n", sort(keys(%$list)));
            print FH "\n"  if scalar(keys(%$list));
            close FH;
        }

        return 1;
    }

    sub servers { return _etc('servers'); }

    sub clients { return _etc('clients'); }

    sub _etc {

        my $name = shift;
        my $ETC  = $Vintage::C::Util::Env::ETC;

        unless (exists $ETC->{$name}) {

            my $fp = $ENV{VTG_ROOT} . "/etc/$name";
            my @fd = ();

            if (-f $fp) {
                open FH, "< $fp";
                while (my $line = <FH>) {
                    chomp $line;
                    push @fd, $line;
                }
                close FH;
            }

            $ETC->{$name} = \@fd;
        }

        return $ETC->{$name};
    }

    sub area    { return _fd($ENV{VTG_ROOT} . '/etc/area'   )->[0] || ''; }
    sub host    { return _fd($ENV{VTG_ROOT} . '/etc/host'   )->[0] || ''; }
    sub manager { return _fd($ENV{VTG_ROOT} . '/etc/manager')->[0] || ''; }
    sub roles   { return _fd($ENV{VTG_ROOT} . '/etc/roles'  );            }
    sub apps    { return _fd($ENV{VTG_ROOT} . '/etc/apps'   );            }
    sub tags    { return _fd($ENV{VTG_ROOT} . '/etc/tags'   );            }

    sub _fd {
        my $fp = shift;
        my @fd = ();
        if (-f $fp) {
            open(FH, "< $fp");
            while (my $line = <FH>) {
                chomp($line);
                push(@fd, $line);
            }
            close(FH);
        }
        return \@fd;
    }
}

1;
