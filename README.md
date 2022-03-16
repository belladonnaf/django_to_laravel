# django_to_laravel

How to use.

cd laravel_folder
php django_to_laravel.php

django folder should be like this.

django folder  => ../python_folder
laravel folder => ../laravel_folder

cd ./laravel_folder

php django_to_laravel.php

We assume folder architecture like below.

$this->conjugate = [];

template, and js will be coupled with file name.

if both are same file name, conjugate take that.

$this->rules = [];

regex pattern that need to replace.

$this->exclude_dirs = ['.', '..', '.git', 'natoo', 'static', 'templates', 'assets', 'node_modules', 'auth2', 'build', 'src', 'main', 'image', 'webpack.config.js'];

directory need to be avoid parsing.

$this->program_name = 'NatooManager';

django application name.

$this->section_replacer = ['main' => 'content'];

section name want to be replace.

$this->main_routine = '';

main_routine code for controller main part.

$this->sub_routine = [];

sub_routine code for controller view part.

$this->routine_mapper = ['index' => ['/.*index.*/m', 'list', 'model']];
        
routine_mapper decide to which file name goes which routine.

$this->default_routine = 'detail';

default_routine decide rest file name goes.

$this->template_folder = 'templates';

template_folder django template located folder name. usually templates.

$this->static_folder = 'static';

static_folder django js file located folder name.

$this->resources_path = 'resources/views';

resources_path laravel resource located path.

$this->controller_path = 'app/Http/Controllers';

controller_path laravel controller located path.

$this->all_module = [];
$this->all_prefix = [];

for debugging

