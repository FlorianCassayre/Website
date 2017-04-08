cp version.template.php version.php

commit=$(git log --format="%H" -n 1)
date=$(git log -1 --format=%cd)

#echo $commit
#echo $date

rep_commit="{COMMIT}"
rep_date="{DATE}"

sed -i "s/${commit}/${rep_commit}/g" version.php
sed -i "s/${date}/${rep_date}/g" version.php