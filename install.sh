cd /home/florian/www/cassayre

cp version.template.php version.php

commit=$(git log --format="%H" -n 1)
date=$(git log -1 --format=%cd)

#echo $commit
#echo $date

rep_commit="{COMMIT}"
rep_date="{DATE}"

sed -i "s/${rep_commit}/${commit}/g" version.php
sed -i "s/${rep_date}/${date}/g" version.php