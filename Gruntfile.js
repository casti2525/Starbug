/*global module:false*/
module.exports = function(grunt) {

	var db = grunt.file.readJSON('app/etc/db/default.json');
	var local = {
		title: "Local",
		database:db.db,
		user:db.username,
		pass:db.password,
		host:db.host
	};

	// Project configuration.
	grunt.initConfig({
		phplint: {
			all: ['app/**/*.php', 'modules/**/*.php', 'core/**/*.php', 'util/*.php']
		},
		jsvalidate: {
			all: {
				files: {
					src:['app/**/*.js', 'core/**/*.js', 'modules/**/*.js']
				}
			}
		},
		jshint: {
			options: {
				force: true,
				eqeqeq: true,
				immed: true,
				latedef: true,
				noarg: true,
				undef: true,
				browser: true,
				unused: true,
				eqnull: true,
				globals: {
					define: true,
					require: true,
					WEBSITE_URL:true
				}
			},
			local: {
				src: ['.']
			},
			ci: {
				src: ['.'],
				options:{
					reporter:'jslint',
					reporterOutput:'build/logs/jslint.xml'
				}
			}
		},
		lesslint: {
			src: ['app/**/custom-screen.less'],
			options: {
				failOnError: false,
				csslint:{
					'adjoining-classes': false
				},
				formatters: [{id: 'lint-xml', dest: 'build/logs/lesslint.xml'}]
			}
		},
		shell: {
			'phploc': {
				command: 'phploc --count-tests --log-csv build/logs/phploc.csv --log-xml build/logs/phploc.xml app core modules'
			},
			'phpcpd': {
				command: 'phpcpd --log-pmd build/logs/pmd-cpd.xml app core modules || true'
			}
		},
		phpmd: {
			local: {
				dir: '.',
				options: {
					reportFormat: 'text',
					exclude:'util,libraries,var,node_modules',
					rulesets:'etc/phpmd.xml'
				}
			},
			ci: {
				dir: '.',
				options: {
					reportFormat: 'xml',
					reportFile: 'build/logs/phpmd.xml',
					exclude: 'util,libraries,var,node_modules',
					rulesets: 'etc/phpmd.xml'
				}
			}
		},
		phpcs: {
			local: {
				dir: ['core', 'app', 'modules'],
				options: {
					extensions: 'php',
					ignore: 'views,templates,layouts,forms',
					standard: 'etc/phpcs.xml',
					ignoreExitCode: true
				}
			},
			ci: {
				dir: ['core', 'app', 'modules'],
				options: {
					extensions: 'php',
					ignore: 'views,templates,layouts,forms',
					standard: 'etc/phpcs.xml',
					ignoreExitCode: true,
					report: 'checkstyle',
					reportFile: 'build/logs/checkstyle.xml'
				}
			}
		},
		phpunit: {
			all: {
				options: {
					configuration: "etc/phpunit.xml"
				}
			}
		},
		intern: {
			local: {
				 options: {
					 runType: 'runner',
					 reporters: ['pretty'],
					 config: 'core/app/public/js/tests/intern'
				 }
			},
			ci: {
				 options: {
					 runType: 'runner',
					 reporters: ['cobertura'],
					 config: 'core/app/public/js/tests/intern'
				 }
			}
		},
		deployments: {
			options:{
				backups_dir: "backups"
			},
			local:local
		},
		rsync: {
			options: {
				args: ["--verbose"],
				exclude: [".git", "node_modules"],
				recursive: true
			},
			dev: {
				options: {
					ssh:true,
					src:"remote:/path/to/app/public/uploads/",
					dest:"./app/public/uploads/",
					delete:true
				}
			}
		}
	});

	// These plugins provide necessary tasks.
	grunt.loadNpmTasks('grunt-phplint');
	grunt.loadNpmTasks('grunt-jsvalidate');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-lesslint');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-phpmd');
	grunt.loadNpmTasks('grunt-phpcs');
	grunt.loadNpmTasks('grunt-phpunit');
	grunt.loadNpmTasks('intern');
	grunt.loadNpmTasks('grunt-deployments');
  grunt.loadNpmTasks('grunt-rsync');

	grunt.registerTask('lint', ['phplint', 'jsvalidate', 'jshint:local', 'lesslint']);
	grunt.registerTask('lint-ci', ['phplint', 'jsvalidate', 'jshint:ci', 'lesslint']);

	grunt.registerTask('local', ['lint', 'shell:phploc', 'phpmd:local', 'phpcs:local', 'shell:phpcpd', 'phpunit', 'intern:local']);
	grunt.registerTask('ci', ['lint-ci', 'shell:phploc', 'phpmd:ci', 'phpcs:ci', 'shell:phpcpd', 'phpunit', 'intern:ci']);

	grunt.registerTask('default', ['local']);

};
