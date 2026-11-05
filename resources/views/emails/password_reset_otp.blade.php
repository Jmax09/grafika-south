<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Password Reset — Grafika South</title>
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

                    <!-- ── HEADER ────────────────────────────────────────── -->
                    <tr>
                        <td style="background: #0F172A; padding: 32px 36px 28px; text-align: center;
                                   border-bottom: 4px solid #0099FF;">

                            <!-- Brand wordmark -->
                            <div style="display: inline-block; margin-bottom: 10px;">
                                <span style="font-size: 26px; font-weight: 900; color: #FFFFFF;
                                             letter-spacing: -0.5px; font-family: 'Segoe UI', Arial, sans-serif;">
                                    GRAFIKA
                                    <span style="color: #0099FF;">SOUTH</span>
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- ── BODY ─────────────────────────────────────────── -->
                    <tr>
                        <td style="padding: 36px 36px 28px;">

                            <!-- Greeting & intro -->
                            <h2 style="margin: 0 0 16px; font-size: 20px; font-weight: 800;
                                       color: #0F172A; line-height: 1.3;">
                                Reset Your Password
                            </h2>

                            <p style="margin: 0 0 10px; font-size: 15px; color: #334155; line-height: 1.7;">
                                Hello <strong style="color: #0F172A;">{{ $user->name }}</strong>,
                            </p>

                            <p style="margin: 0 0 24px; font-size: 15px; color: #475569; line-height: 1.7;">
                                We received a request to reset your password for your Grafika South account
                                (<span style="color: #0099FF; font-weight: 600;">{{ $user->email }}</span>).
                                Please use the verification code below to complete your password reset:
                            </p>

                            <!-- ── OTP CODE CARD ──────────────────────── -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="margin-bottom: 28px;">
                                <tr>
                                    <td style="background: #F1F8FF; border: 1.5px solid #BAE6FD;
                                               border-radius: 12px; padding: 28px 24px; text-align: center;">

                                        <p style="margin: 0 0 10px; font-size: 11px; font-weight: 700;
                                                  text-transform: uppercase; letter-spacing: 1.2px;
                                                  color: #0369A1;">
                                            Your 6-Digit Verification Code
                                        </p>

                                        <!-- OTP digits -->
                                        <p style="margin: 0; font-family: 'Courier New', 'Lucida Console', monospace;
                                                  font-size: 44px; font-weight: 900; letter-spacing: 14px;
                                                  color: #0F172A; line-height: 1;">
                                            {{ $otp }}
                                        </p>

                                        <p style="margin: 14px 0 0; font-size: 12px; color: #64748B;
                                                  font-weight: 500;">
                                            Valid for <strong style="color: #0369A1;">15 minutes</strong>
                                            &nbsp;·&nbsp; Do not share this code with anyone
                                        </p>

                                    </td>
                                </tr>
                            </table>
                            <!-- ── /OTP CODE CARD ─────────────────────── -->

                            <!-- Expiry / security notice -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="margin-bottom: 8px;">
                                <tr>
                                    <td style="background: #FFFBEB; border: 1px solid #FDE68A;
                                               border-radius: 10px; padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400E; line-height: 1.6;">
                                            <strong>&#9888;&#65039; Security Notice:</strong>
                                            If you did not request a password reset, please ignore this email.
                                            Your password will remain unchanged. For any concerns,
                                            contact us directly at
                                            <a href="mailto:grafikasouth69@gmail.com"
                                               style="color: #92400E; font-weight: 700;">grafikasouth69@gmail.com</a>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- ── DIVIDER ───────────────────────────────────────── -->
                    <tr>
                        <td style="padding: 0 36px;">
                            <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 0;">
                        </td>
                    </tr>

                    <!-- ── FOOTER ────────────────────────────────────────── -->
                    <tr>
                        <td style="background: #F8FAFC; padding: 22px 36px; text-align: center;
                                   border-top: none; border-radius: 0 0 16px 16px;">

                            <!-- Brand mini -->
                            <p style="margin: 0; font-size: 13px; font-weight: 800;
                                      color: #0F172A; letter-spacing: 0.2px;">
                                GRAFIKA <span style="color: #0099FF;">SOUTH</span>
                            </p>

                        </td>
                    </tr>

                </table>
                <!-- /Email card -->

            </td>
        </tr>
    </table>

</body>
</html>
