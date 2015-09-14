# record
git stash -u

# deploy to demo
scp -i ~/.ssh/id_rsa -prq . $DEPLOY_USER@aplazame.com:$WOO_PATH
