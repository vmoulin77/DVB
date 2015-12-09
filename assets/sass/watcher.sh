#!/bin/bash

assets_path="/var/www/html/DVB/assets/"

sass[0]="sass/app.scss"
css[0]="css/app.css"

sass[1]="sass/controllers/Card/actions/search.scss"
css[1]="css/controllers/Card/actions/search.css"

sass[2]="sass/controllers/Card/actions/create_edit.scss"
css[2]="css/controllers/Card/actions/create_edit.css"

sass[3]="sass/controllers/Card/controller.scss"
css[3]="css/controllers/Card/controller.css"

sass[4]="sass/controllers/Card/actions/view.scss"
css[4]="css/controllers/Card/actions/view.css"

watcher="sass --watch --cache-location $HOME/.cache/sass --sourcemap=none"

for (( i=0; i < ${#sass[@]}; i++ )); do
    watcher="${watcher} ${assets_path}${sass[i]}:${assets_path}${css[i]}"
done

${watcher}
