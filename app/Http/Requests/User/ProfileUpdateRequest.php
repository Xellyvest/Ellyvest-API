<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_id' => 'sometimes|uuid|exists:countries,id',
            'state_id' => 'sometimes|uuid|exists:states,id',
            'city_id' => 'sometimes|uuid|exists:cities,id',
            'city' => 'sometimes|string|max:255',
            'currency_id' => 'sometimes|uuid|exists:currencies,id',
            'first_name' => 'sometimes|string|max:191',
            'last_name' => 'sometimes|string|max:191',
            'username' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'username'),
            ],
            'phone' => 'sometimes|string|max:15',
            'address' => 'sometimes|string|max:255',
            'zipcode' => 'sometimes|string|max:20',
            'ssn' => 'sometimes|string|max:20',
            'dob' => 'nullable|date',
            'nationality' => 'sometimes|string|max:191',
        ];
    }
}


    server {
        root /var/www/admin/dist;
        index index.html index.htm;

        server_name admin.firestormsolution.com www.admin.firestormsolution.com;

        location / {
            try_files $uri $uri/ /index.html;
        }

    }
    server {
        if ($host = admin.firestormsolution.com) {
            return 301 https://$host$request_uri;
        } # managed by Certbot


        listen 80;
        listen [::]:80;

        server_name admin.firestormsolution.com www.admin.firestormsolution.com;
        return 404; # managed by Certbot


    }


server {
    server_name 146.190.55.57;
    root /var/www/dump;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

}


server {
    root /var/www/admin-staging/dist;
    index index.html index.htm;

    server_name admin-staging.firestormsolution.com www.admin-staging.firestormsolution.com;

    location / {
        try_files $uri $uri/ /index.html;
    }

}
server {
    if ($host = admin-staging.firestormsolution.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    listen 80;
    listen [::]:80;

    server_name admin-staging.firestormsolution.com www.admin-staging.firestormsolution.com;
    return 404; # managed by Certbot


}







server {
    root /var/www/web/dist;
    index index.html index.htm;

    server_name firestormsolution.com;

    location / {
        try_files $uri $uri/ /index.html;
    }

}
server {
    if ($host = firestormsolution.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    if ($host = firestormsolution.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    listen 80;
    listen [::]:80;

    server_name firestormsolution.com;
    return 404; # managed by Certbot

}