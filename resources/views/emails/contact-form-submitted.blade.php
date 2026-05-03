<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .field {
            margin-bottom: 20px;
        }
        .label {
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .value {
            background: white;
            padding: 10px;
            border-left: 4px solid #4F46E5;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Contact Form Submission</h1>
        <p>AI PR Inspector - Contact Form</p>
    </div>

    <div class="content">
        <p>You have received a new message from the AI PR Inspector contact form:</p>

        <div class="field">
            <div class="label">Name:</div>
            <div class="value">{{ $formData['name'] }}</div>
        </div>

        <div class="field">
            <div class="label">Email:</div>
            <div class="value">{{ $formData['email'] }}</div>
        </div>

        <div class="field">
            <div class="label">Subject:</div>
            <div class="value">{{ $formData['subject'] }}</div>
        </div>

        <div class="field">
            <div class="label">Message:</div>
            <div class="value">{{ nl2br($formData['message']) }}</div>
        </div>

        @if(isset($formData['newsletter']) && $formData['newsletter'])
        <div class="field">
            <div class="label">Newsletter Subscription:</div>
            <div class="value">Yes, this person would like to receive updates.</div>
        </div>
        @endif

        <div class="field">
            <div class="label">Submitted:</div>
            <div class="value">{{ now()->format('Y-m-d H:i:s') }}</div>
        </div>
    </div>

    <div class="footer">
        <p>This message was sent from the AI PR Inspector contact form.</p>
        <p>Please respond to the sender at their email address above.</p>
    </div>
</body>
</html>
