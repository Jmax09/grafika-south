GRAFIKA SOUTH - STAFF ADMINISTRATOR ACCOUNT READY

Hello {{ $user->name }},

A Staff Administrator account has been created for you at Grafika South.

LOGIN DETAILS:
- Login Email: {{ $user->email }}
- Staff Username: @{{ $user->username }}
- Portal URL: {{ url('/admin/login') }}

YOUR TEMPORARY PASSWORD:
{{ $password }}

HOW TO LOG IN FOR THE FIRST TIME:
1. Visit {{ url('/admin/login') }}
2. Sign in using your email or username and the temporary password above.
3. You will be prompted to set a new password before accessing the portal.

Security tip: This temporary password was generated for you. Please keep it
private and change it immediately upon signing in.
