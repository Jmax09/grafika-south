<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Your Grafika South Staff Account — Ready</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F0F4F8; font-family: 'Segoe UI', Arial, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

    <!-- Outer wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color: #F0F4F8; padding: 40px 16px;">
        <tr>
            <td align="center">

                <!-- Email card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 580px; background: #FFFFFF; border-radius: 16px; overflow: hidden;
                              border: 1px solid #E2E8F0; box-shadow: 0 8px 24px rgba(0,0,0,0.07);">

                    <!-- HEADER -->
                    <tr>
                        <td style="background: #0F172A; padding: 32px 36px 28px; text-align: center;
                                   border-bottom: 4px solid #0099FF;">

                            <div style="display: inline-block; margin-bottom: 10px;">
                                <span style="font-size: 26px; font-weight: 900; color: #FFFFFF;
                                             letter-spacing: -0.5px; font-family: 'Segoe UI', Arial, sans-serif;">
                                    GRAFIKA
                                    <span style="color: #0099FF;">SOUTH</span>
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding: 36px 36px 28px;">

                            <h2 style="margin: 0 0 14px; font-size: 20px; font-weight: 800;
                                       color: #0F172A; line-height: 1.3;">
                                Welcome to the Grafika South Staff Team!
                            </h2>

                            <p style="margin: 0 0 10px; font-size: 15px; color: #334155; line-height: 1.7;">
                                Hello <strong style="color: #0F172A;">{{ $user->name }}</strong>,
                            </p>

                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569; line-height: 1.7;">
                                A <strong>Staff Administrator</strong> account has been created for you at Grafika South.
                                Use the credentials below to sign in for the first time.
                            </p>

                            <!-- Credentials Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="margin-bottom: 24px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 18px;">
                                <tr>
                                    <td>
                                        <div style="font-size: 12px; color: #64748B; margin-bottom: 4px;">Login Email: <strong style="color: #0F172A;">{{ $user->email }}</strong></div>
                                        <div style="font-size: 12px; color: #64748B; margin-bottom: 4px;">Staff Username: <strong style="color: #0099FF;">@{{ $user->username }}</strong></div>
                                        <div style="font-size: 12px; color: #64748B;">Portal Login: <strong style="color: #0F172A;">{{ url('/admin/login') }}</strong></div>
                                    </td>
                                </tr>
                            </table>

                            <!-- PASSWORD CARD -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="margin-bottom: 24px;">
                                <tr>
                                    <td style="background: #F1F8FF; border: 1.5px solid #BAE6FD;
                                               border-radius: 12px; padding: 24px 20px; text-align: center;">

                                        <p style="margin: 0 0 8px; font-size: 11px; font-weight: 700;
                                                  text-transform: uppercase; letter-spacing: 1.2px;
                                                  color: #0369A1;">
                                            Your Temporary Password
                                        </p>

                                        <p style="margin: 0; font-family: 'Courier New', 'Lucida Console', monospace;
                                                  font-size: 26px; font-weight: 900; letter-spacing: 2px;
                                                  color: #0F172A; line-height: 1.2; word-break: break-all;">
                                            {{ $password }}
                                        </p>

                                        <p style="margin: 12px 0 0; font-size: 12px; color: #64748B; font-weight: 500;">
                                            You will be asked to set a new password right after your first login.
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/admin/login') }}"
                                           style="display: inline-block; background-color: #0099FF; color: #FFFFFF; font-size: 14px; font-weight: 700; text-decoration: none; padding: 12px 28px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 153, 255, 0.25);">
                                            Sign In Now &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Instructions -->
                            <div style="background: #F8FAFC; border-left: 3px solid #0099FF; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px;">
                                <p style="margin: 0 0 6px; font-size: 12px; font-weight: 700; color: #0F172A;">How to log in for the first time:</p>
                                <ol style="margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                                    <li>Visit <a href="{{ url('/admin/login') }}" style="color: #0099FF;">{{ url('/admin/login') }}</a></li>
                                    <li>Sign in using your email or username (<strong>{{ $user->username }}</strong>) and the temporary password above</li>
                                    <li>You will be prompted to <strong>set a new password</strong> before accessing the portal</li>
                                </ol>
                            </div>

                            <!-- Security note -->
                            <div style="background: #FFFBEB; border: 1px solid #FDE68A; padding: 12px 16px; border-radius: 8px;">
                                <p style="margin: 0; font-size: 12px; color: #92400E; line-height: 1.6;">
                                    <strong>Security tip:</strong> This temporary password was generated for you. Please keep it private and change it immediately upon signing in.
                                </p>
                            </div>

                        </td>
                    </tr>

                    <!-- DIVIDER -->
                    <tr>
                        <td style="padding: 0 36px;">
                            <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 0;">
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background: #F8FAFC; padding: 22px 36px; text-align: center;
                                   border-top: none; border-radius: 0 0 16px 16px;">
                            <p style="margin: 0; font-size: 13px; font-weight: 800; color: #0F172A;">
                                GRAFIKA <span style="color: #0099FF;">SOUTH</span>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
