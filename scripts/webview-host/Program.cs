using System;
using System.Drawing;
using System.IO;
using System.Runtime.InteropServices;
using System.Windows.Forms;
using Microsoft.Web.WebView2.WinForms;

namespace ExteriorStudentHost
{
    internal static class Program
    {
        private const string AppUserModelId = "ExteriorStudent.DesktopApp";

        [DllImport("shell32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
        private static extern int SetCurrentProcessExplicitAppUserModelID(string appID);

        [STAThread]
        private static void Main(string[] args)
        {
            try
            {
                SetCurrentProcessExplicitAppUserModelID(AppUserModelId);
            }
            catch
            {
                // ignore on older systems
            }

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            string url = "http://exterior_student.test";
            if (args != null && args.Length > 0 && !string.IsNullOrWhiteSpace(args[0]))
            {
                url = args[0].Trim();
            }

            string baseDir = AppDomain.CurrentDomain.BaseDirectory;
            string iconPath = Path.Combine(baseDir, "ExteriorStudent.ico");

            Form form = new Form();
            form.Text = "نظام إدارة الطلبة الخارجيين";
            form.StartPosition = FormStartPosition.CenterScreen;
            form.WindowState = FormWindowState.Maximized;
            form.MinimumSize = new Size(900, 600);
            form.ShowIcon = true;
            form.ShowInTaskbar = true;

            if (File.Exists(iconPath))
            {
                try
                {
                    form.Icon = new Icon(iconPath);
                }
                catch
                {
                    // ignore icon load errors
                }
            }

            WebView2 web = new WebView2();
            web.Dock = DockStyle.Fill;
            form.Controls.Add(web);

            form.Shown += async (sender, e) =>
            {
                try
                {
                    string userData = Path.Combine(
                        Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
                        "ExteriorStudent",
                        "WebView2");
                    Directory.CreateDirectory(userData);

                    string origin = url;
                    try
                    {
                        origin = new Uri(url).GetLeftPart(UriPartial.Authority);
                    }
                    catch
                    {
                        // keep the launch url if it is not a full URI
                    }

                    var options = new Microsoft.Web.WebView2.Core.CoreWebView2EnvironmentOptions(
                        additionalBrowserArguments: "--unsafely-treat-insecure-origin-as-secure=" + origin);
                    var env = await Microsoft.Web.WebView2.Core.CoreWebView2Environment.CreateAsync(
                        browserExecutableFolder: null,
                        userDataFolder: userData,
                        options: options);
                    await web.EnsureCoreWebView2Async(env);
                    web.CoreWebView2.Settings.AreDefaultContextMenusEnabled = true;
                    web.CoreWebView2.Settings.AreDevToolsEnabled = false;
                    web.CoreWebView2.Settings.IsZoomControlEnabled = true;
                    web.CoreWebView2.DownloadStarting += (downloadSender, downloadArgs) =>
                    {
                        using (SaveFileDialog dialog = new SaveFileDialog())
                        {
                            string suggested = downloadArgs.ResultFilePath;
                            bool isBackup = string.Equals(
                                Path.GetExtension(suggested),
                                ".esbak",
                                StringComparison.OrdinalIgnoreCase)
                                || string.Equals(
                                Path.GetExtension(suggested),
                                ".sql",
                                StringComparison.OrdinalIgnoreCase);

                            dialog.Title = isBackup ? "حفظ النسخة الاحتياطية" : "حفظ الملف";
                            dialog.Filter = isBackup
                                ? "نسخة مشفّرة (*.esbak)|*.esbak|SQL (*.sql)|*.sql|كل الملفات (*.*)|*.*"
                                : "كل الملفات (*.*)|*.*";
                            dialog.DefaultExt = isBackup ? "esbak" : "";
                            dialog.AddExtension = isBackup;
                            dialog.OverwritePrompt = true;
                            dialog.RestoreDirectory = true;
                            dialog.InitialDirectory = Environment.GetFolderPath(
                                Environment.SpecialFolder.DesktopDirectory);
                            dialog.FileName = string.IsNullOrWhiteSpace(suggested)
                                ? (isBackup ? "backup.esbak" : "download")
                                : Path.GetFileName(suggested);

                            if (dialog.ShowDialog(form) == DialogResult.OK)
                            {
                                downloadArgs.ResultFilePath = dialog.FileName;
                            }
                            else
                            {
                                downloadArgs.Cancel = true;
                            }
                        }
                    };
                    web.CoreWebView2.Navigate(url);
                }
                catch (Exception ex)
                {
                    MessageBox.Show(
                        "تعذر فتح البرنامج داخل العارض." + Environment.NewLine +
                        "تأكد من تشغيل Herd و MySQL ثم أعد المحاولة." + Environment.NewLine +
                        Environment.NewLine + ex.Message,
                        "نظام الطلبة",
                        MessageBoxButtons.OK,
                        MessageBoxIcon.Error);
                    form.Close();
                }
            };

            Application.Run(form);
        }
    }
}
