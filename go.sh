 ======== Firefox option to change ========
browser.tabs.loadDivertedInBackground
dom.popup_maximum

export MSYS_NO_PATHCONV=1
docker run --rm -p 8080:80 -e FRESHRSS_ENV=development -e TZ=Europe/Paris -e 'CRON_MIN=1,31' -v $(pwd):/var/www/FreshRSS -v freshrss_data:/var/www/FreshRSS/data --name freshrss   freshrss/freshrss:edge