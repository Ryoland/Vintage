package Vintage::C::Etc; {

    use strict;
    use warnings;
    use utf8;

    use Vintage::C::Util::Env;

    sub fps_conf {

        my $a         = shift           || return [];
        my $project   = $a->{project}   || return [];
        my $program   = $a->{program}   || return [];
        my $extension = $a->{extension} || 'conf';
        my $if_exists = $a->{if_exists} || 0;

        my $area = Vintage::C::Util::Env::area();
        my $host = Vintage::C::Util::Env::host();

        my %categories = (
            host    => [$area, "$area/$host"],
            roles   => Vintage::C::Util::Env::roles(),
            servers => Vintage::C::Util::Env::servers(),
            clients => Vintage::C::Util::Env::clients(),
            apps    => Vintage::C::Util::Env::apps(),
            tags    => Vintage::C::Util::Env::tags()
        );

        my $dp = sprintf(
            '%s/pro/%s/Vintage/etc/%s',
            $ENV{VTG_ROOT},
            $project,
            $program
        );

        my @fps = ();
        my ($fp, $item, $list, $name);

        if (!$if_exists || (-f "$dp/global.$extension")) {
            @fps = ("$dp/global.$extension");
        }

        while (($name, $list) = each(%categories)) {
            if (scalar @$list) {
                for $item (@$list) {
                    $fp = "$dp/$name/$item.$extension";
                    if (!$if_exists || (-f $fp)) {
                        push(@fps, $fp);
                    }
                }
            }
        }

        return \@fps;
    }
}

1;
