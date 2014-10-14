package Vintage::C::Util::Packager; {

    ##========================================================================
    ## Use
    ##========================================================================

    use strict;
    use warnings;
    use utf8;

    use YAML::Syck;
    use Vintage::C::Etc;
    use Vintage::C::Util::Env;
    use Vintage::C::Util::Packager::Yum;

    ##========================================================================
    ## Our
    ##========================================================================

    ##========================================================================
    ## Sub
    ##========================================================================

    sub run {

        my $a         = shift;
        my $project   = $a->{project}   || Vintage::C::Util::Env::manager();
        my $extension = $a->{extension} || 'yml';

        my $fps_conf = Vintage::C::Etc::fps_conf({
            project   => $project,
            program   => 'manage/package',
            extension => $extension,
            if_exists => 1
        });

        my $package = '';
        my $data    = {};
        my $confs   = {};
        my %confs   = ();
        my $res     = {};

        for my $fp_conf (@$fps_conf) {

            $data = YAML::Syck::LoadFile($fp_conf) || next;

            if ($data->{conf}) {
                while (($package, $confs) = each(%{$data->{conf}})) {
                    push(@{$confs{$package} ||= []}, $confs);
                }
            }

            while (($package, $confs) = each(%confs)) {
                if ($package eq 'yum') {
                    $res = Vintage::C::Util::Packager::Yum::run({confs => $confs});
                }
            }
        }

        return 1;
    }

    ##========================================================================
}

1;
