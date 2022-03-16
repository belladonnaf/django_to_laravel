<?php

class django_to_laravel
{
    public function __construct()
    {
        $this->conjugate = [];
        $this->rules = [];

        $this->exclude_dirs = ['.', '..', '.git', 'natoo', 'static', 'templates', 'assets', 'node_modules', 'auth2', 'build', 'src', 'main', 'image', 'webpack.config.js'];
        $this->program_name = 'NatooManager';
        $this->section_replacer = ['main' => 'content'];

        $this->main_routine = '';
        $this->sub_routine = [];
        $this->routine_mapper = ['index' => ['/.*index.*/m', 'list', 'model']];
        $this->default_routine = 'detail';

        $this->template_folder = 'templates';
        $this->static_folder = 'static';
        $this->resources_path = 'resources/views';
        $this->controller_path = 'app/Http/Controllers';
        $this->all_module = [];
        $this->all_prefix = [];

        $this->route_template = '';
        $this->route_file = 'routes/manager.php';
        $this->middleware_mapper = [];
        $this->default_middleware = '->middleware(\'session.has.user\')';

        $this->route_alias = [ ['index'=>'/'], ['_index'=>'/'], ['_update'=>'/update'] ];

        $this->routine_mapper = array_reverse($this->routine_mapper);

    }

    public function run()
    {
        $this->set_routines();
        $this->parse_django_files();
        $this->attach_rules();
        $this->translate();
    }

    public function print_modules()
    {
        $this->parse_django_files();

        foreach ($this->all_module as $module) {
            echo $module."\n";
        }
    }

    public function print_prefix()
    {
        $this->parse_django_files();

        foreach ($this->all_prefix as $prefix) {
            echo $prefix."\n";
        }
    }

    public function print_conjugate()
    {
        $this->parse_django_files();
        var_dump($this->conjugate);
    }

    public function set_routines()
    {
        $main_routine = <<<EOF
        <?php
        
        namespace App\Http\Controllers;
        
        use Illuminate\Http\Request;
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Cache;
        
        class [Controller] extends Controller
        {
        [Functions]
        }
        EOF;

        $sub_routine_index = <<<EOF
        
            public function [Function](Request \$request)
            {
                \$arr_rs = [];
                return view('[View]', compact('arr_rs'));
        
            }
        EOF;

        $sub_routine_detail = <<<EOF
        
            public function [Function](Request \$request)
            {
                \$row = [];
                return view('[View]', compact('row'));
        
            }
        EOF;

        $route_template = <<<EOF
<?php

[Imports]
use Illuminate\Support\Facades\Route;


[Routers]

EOF;

        $this->main_routine = $main_routine;
        $this->sub_routine = ['index' => $sub_routine_index, 'detail' => $sub_routine_detail];
        $this->route_template = $route_template;

    }


    public function attach_rules()
    {
        $re = '/\{\%[ ]*extends[ ]*[\'"](\w+)\.html[\'"][ ]*\%\}/m';
        $rpl = <<<EOF
@extends('layouts.\$1')
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*block[ ]*([a-zA-Z]*)[ ]*\%\}(.*)\{\%[ ]*endblock[ ]*\%\}/m';
        $rpl = <<<EOF
@section('\$1','\$2')
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*block[ ]*([a-zA-Z]*)[ ]*\%\}/m';
        $rpl = <<<EOF
@section('\$1')
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*verbatim[ ]*\%\}/m';
        $rpl = <<<EOF
@verbatim
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*endverbatim[ ]*\%\}/m';
        $rpl = <<<EOF
@endverbatim
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*endblock[ ]*\%\}/m';
        $rpl = <<<EOF
@endsection
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*load[ ]*(\w)+[ ]*\%\}/m';
        $rpl = '';
        $this->add_rule($re, $rpl);

        $re = '/{\%[ ]*static[ ]*[\'|"]([^%]*)[\'|"][ ]*\%\}/m';
        $rpl = <<<EOF
/static/\$1
EOF;        
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*if([^%]*)\%\}/m';
        $rpl = <<<EOF
@if (\$1);
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*endif[ ]*\%\}/m';
        $rpl = <<<EOF
@endif;
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*else[ ]*\%\}/m';
        $rpl = <<<EOF
@else;
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*for([^%]*)\%\}/m';
        $rpl = <<<EOF
@for (\$1);
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*endfor[ ]*\%\}/m';
        $rpl = <<<EOF
@endfor;
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*csrf_token[ ]*\%\}/m';
        $rpl = <<<EOF
@csrf;
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\%[ ]*elif([^%]*)\%\}/m';
        $rpl = <<<EOF
@elif (\$1);
EOF;
        $this->add_rule($re, $rpl);

        $re = '/{\%[ ]*url[ ]*[\'|"]([^%]*):list[\'|"][ ]*\%\}/m';
        $rpl = <<<EOF
{{ route('\$1.index') }}
EOF;
        $this->add_rule($re, $rpl);

        $re = '/{\%[ ]*url[ ]*[\'|"]([^%]*)[\'|"][ ]*\%\}/m';
        $rpl = <<<EOF
{{ route('\$1') }}
EOF;
        $this->add_rule($re, $rpl);

        $re = '/\{\{[ ]*request\.GET\.(\w+)[ ]*\}\}/m';
        $rpl = <<<EOF
{{ request()->get('\$1') }}
EOF;
        $this->add_rule($re, $rpl);

    }

    public function add_rule($pattern, $replace)
    {
        $this->rules[] = ['pattern' => $pattern, 'replace' => $replace];
    }

    public function parse_django_files()
    {
        $default_path = __DIR__.'/../'.$this->program_name;
        $target_path = __DIR__;

        $modules = [];
        $templates = [];
        $js_files = [];

        $h1 = opendir($default_path);
        while (false !== ($f1 = readdir($h1))) {
            if (array_search($f1, $this->exclude_dirs) === false) {
                // realpath
                $fc1 = $default_path.'/'.$f1;
                $fc1 = realpath($fc1);

                if (is_dir($fc1)) {
                    $modules[] = $f1;
                }

                foreach ($modules as $module) {
                    $template_path = $fc1.'/'.$this->template_folder.'/'.$module;

                    if (is_dir($template_path)) {
                        $h2 = opendir($template_path);

                        while (false !== ($f2 = readdir($h2))) {
                            $re = '/[a-zA-Z_-]+\.html/m';
                            preg_match_all($re, $f2, $matches, PREG_SET_ORDER, 0);

                            if (isset($matches[0][0])) {
                                $template_file = $matches[0][0];

                                if (count(explode('bak', $template_file)) == 1) {
                                    $prefix = explode('.', $template_file)[0];
                                    $templates[$module][] = ['file_path' => $template_path.'/'.$template_file, 'file_name' => $template_file, 'prefix' => $prefix];
                                }
                            }
                        }
                    } // template loop

                    $js_path = $fc1.'/'.$this->static_folder.'/'.$module;

                    if (is_dir($js_path)) {
                        $h2 = opendir($js_path);

                        while (false !== ($f2 = readdir($h2))) {
                            $re = '/[a-zA-Z_-]+\.js/m';
                            preg_match_all($re, $f2, $matches, PREG_SET_ORDER, 0);

                            if (isset($matches[0][0])) {
                                $js_file = $matches[0][0];

                                if (count(explode('bak', $js_file)) == 1) {
                                    $prefix = explode('.', $js_file)[0];
                                    $js_files[$module][] = ['file_path' => $js_path.'/'.$js_file, 'file_name' => $js_file, 'prefix' => $prefix];
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($templates) {
            foreach ($templates as $module => $modules) {
                foreach ($modules as $k => $file) {
                    if (isset($js_files[$module])) {
                        foreach ($js_files[$module] as $js) {
                            if ($file['prefix'] == $js['prefix']) {
                                $this->conjugate[$module][$file['prefix']] = ['template_path' => $file['file_path'], 'template_name' => $file['file_name'], 'js_path' => $js['file_path'], 'js_name' => $js['file_name']];
                                if (array_search($file['prefix'], $this->all_prefix) === false) {
                                    $this->all_prefix[] = $file['prefix'];
                                }
                                if (array_search($module, $this->all_module) === false) {
                                    $this->all_module[] = $module;
                                }
                            }
                        }
                    } else {
                        $this->conjugate[$module][$file['prefix']] = ['template_path' => $file['file_path'], 'template_name' => $file['file_name'], 'js_path' => null, 'js_name' => null];
                        if (array_search($file['prefix'], $this->all_prefix) === false) {
                            $this->all_prefix[] = $file['prefix'];
                        }
                        if (array_search($module, $this->all_module) === false) {
                            $this->all_module[] = $module;
                        }
                    }
                }
            }
        }
    }

    public function translate()
    {

        $buffer = '';
        $cnt = 0;

        $base_path = __DIR__.'/';

        $resources_path = $base_path.$this->resources_path;
        $controller_path = $base_path.$this->controller_path;

        if (!is_dir($resources_path)) {
            mkdir($resources_path, 0755, true);
        }

        $arr_cp = explode('/',$this->controller_path);

        foreach($arr_cp as $v){
            $arr_import_path[] = ucfirst($v);
        }

        $import_path = implode('\\',$arr_import_path);

        $str_imports = '';
        $str_routes = '';

        foreach ($this->conjugate as $module => $modules) {
            $cont = ucfirst($module).'Controller';
            unset($func_content);

            $path = $resources_path.'/'.$module;

            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $cnt_prefix = 0;
            $max_prefix = count($modules);

            foreach ($modules as $prefix => $source) {

                $the_routine = '';

                foreach ($this->routine_mapper as $routine => $mapper) {
                    if ($mapper) {
                        foreach ($mapper as $pattern) {
                            if (count(explode('/', $pattern)) > 1) {
                                preg_match_all($pattern, $prefix, $matches, PREG_SET_ORDER, 0);
                                if (isset($matches[0][0])) {
                                    $the_routine = $routine;
                                }
                            } else {
                                if ($routine == $pattern) {
                                    $the_routine = $routine;
                                }
                            }
                        }
                    }
                }

                if (!$the_routine) {
                    $the_routine = $this->default_routine;
                }

                $the_middleware = '';

                foreach ($this->middleware_mapper as $middleware => $mapper) {
                    if ($mapper) {
                        foreach ($mapper as $pattern) {
                            if (count(explode('/', $pattern)) > 1) {
                                preg_match_all($pattern, $prefix, $matches, PREG_SET_ORDER, 0);
                                if (isset($matches[0][0])) {
                                    $the_middleware = $middleware;
                                }
                            } else {
                                if ($routine == $pattern) {
                                    $the_middleware = $middleware;
                                }
                            }
                        }
                    }
                }

                if (!$the_middleware) {
                    $the_middleware = $this->default_middleware;
                }

                $func_content[] = str_replace('[View]', $module.'.'.$prefix, str_replace('[Function]', $prefix, $this->sub_routine[$the_routine]));

                $template_source = file_get_contents($source['template_path'], 'r');

                $cnt_rn = count(explode("\r\n",$template_source));
                $cnt_nn = count(explode("\n",$template_source));

                if($cnt_nn == $cnt_rn){
                    $line_delimiter = "\r\n";
                } else {
                    $line_delimiter = "\n";
                }

                $lines = explode($line_delimiter,$template_source);

                foreach ($this->rules as $rule) {
                    extract($rule);

                    if($lines){

                        foreach($lines as $no => $line){

                            preg_match_all($pattern, $line, $matches, PREG_SET_ORDER, 0);
    
                            $the_replace = $replace;
                            $s = [];
                            for ($i = 5; $i > 0; --$i) {
                                if (count(explode('$'.$i, $replace)) > 1) {
                                    if (isset($matches[0][$i])) {
                                        $s[$i] = $matches[0][$i];
                                        $the_replace = str_replace('$'.$i, $s[$i], $the_replace);
                                    }
                                }
                            }
                        
                            foreach ($this->section_replacer as $src => $rpl) {
                                $the_replace = str_replace($src, $rpl, $the_replace);
                            }

                            $lines[$no] = preg_replace($pattern, $the_replace, $line);
                            
                        }
                    // lines    
                    }
                // rule loop
                }

                if($lines) $template_source = implode($line_delimiter,$lines);
                
                $module_route = $prefix;

                if($this->route_alias){
                    foreach($this->route_alias as $arr_alias){
                        foreach($arr_alias as $alias => $rpl){
                            if($alias == $prefix){
                                $module_route = $rpl;
                            } else if ( count(explode($alias, $prefix)) > 1 ){
                                $module_route = str_replace($alias,$rpl,$prefix);
                            }
                        }
                    }
                }

                if($cnt_prefix == 0){
                    $str_routes .= $line_delimiter."Route::group(['prefix' => '".strtolower($module)."'], function () {".$line_delimiter;
                }

                $str_routes .= "    Route::get('".strtolower($module_route)."', [".$cont."::class, '".$prefix."'])->name('".strtolower($module).".".strtolower($prefix)."');".$line_delimiter;

                if($cnt_prefix == ($max_prefix-1)){
                    $str_routes .= '})';

                    if($the_middleware){
                        $str_routes .= $the_middleware.';'.$line_delimiter;
                    } else {
                        $str_routes .= ';'.$line_delimiter;
                    }
    
                }

                $f = fopen($path.'/'.$prefix.'.blade.php', 'w');
                fwrite($f, '<!-- '.$cont.'::'.$prefix.' -->'."\r\n".$template_source);
                fclose($f);
                echo '*';

                $cnt_prefix++;
            // prefix loop
            }


            if( !isset($line_delimiter) ) $line_delimiter = "\n";

            $str_imports .= 'use '.$import_path.'\\'.$cont.';'.$line_delimiter;

            $controller_code = implode("\r\n", $func_content);
            $the_controller_code = str_replace('[Functions]', $controller_code, str_replace('[Controller]', $cont, $this->main_routine));

            $f = fopen($controller_path.'/'.$cont.'.php', 'w');
            fwrite($f, $the_controller_code);
            fclose($f);
            echo '#';
        // module loop
            
        }

        $this->route_template =str_replace('[Imports]',$str_imports,$this->route_template);
        $this->route_template =str_replace('[Routers]',$str_routes,$this->route_template);

        $f = fopen($base_path.$this->route_file,'w');
        fwrite($f, $this->route_template);
        fclose($f);

    // translate
    }

}

$django_to_laravel = new django_to_laravel();
$django_to_laravel->run();
//$django_to_laravel->print_modules();
//$django_to_laravel->print_prefix();
//$django_to_laravel->print_conjugate();
