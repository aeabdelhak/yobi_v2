module.exports = {
  apps: [{
    name:"graphql preprod",
    script: '',
    exec_mode: "cluster",
    autorestart: true,
    env: {
      NODE_ENV: 'preprod',
      PORT:3012
      },
    watch:true
  }],

  deploy : {
    preprod : {
      user : 'root',
      host: '89.117.37.24',
      ref: 'origin/preprod',
      repo : 'git@github.com:aeabdelhak/yobi_v2.git',
      path : '/var/www/preprod/graphql',
      'post-deploy' : 'sh /usr/bin/dpl_laravel.sh',
    }
  }
};
