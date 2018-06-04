Summary: Package – Lincoln Tech Microsite
Name: wp_lincoln_tech
Version: %version
Release: 1
License: Not Applicable
Group: Development/Library
URL: http://www.jellyfish.net
BuildRoot: %{_tmppath}/%{name}-root
Source0: %{name}-%{version}.tar.gz
Requires: php
BuildArch: noarch
AutoReqProv: no

%description
Lincoln Tech – WordPress Microsite

%prep
%setup -q

%install
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

install -d -m 755 $RPM_BUILD_ROOT/home/sites/%{name}
cp -R * $RPM_BUILD_ROOT/home/sites/%{name}

%post
/sbin/service httpd reload
cd /home/sites/%{name}
/bin/sh scripts/post-upgrade.sh

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,apache,apache,-)
/home/sites/%{name}
