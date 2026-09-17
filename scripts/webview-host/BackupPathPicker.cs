using System;
using System.IO;
using System.Text;
using System.Windows.Forms;

namespace ExteriorStudentHost
{
    internal static class BackupPathPicker
    {
        [STAThread]
        private static int Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            if (args == null || args.Length < 1 || string.IsNullOrWhiteSpace(args[0]))
            {
                return 1;
            }

            string outputPath = args[0].Trim();
            string suggestedName = args.Length > 1 && !string.IsNullOrWhiteSpace(args[1])
                ? Path.GetFileName(args[1].Trim())
                : "backup.esbak";
            string mode = args.Length > 2 && !string.IsNullOrWhiteSpace(args[2])
                ? args[2].Trim().ToLowerInvariant()
                : "save";
            bool openExisting = mode == "open";

            using (OpenFileDialog dialog = new OpenFileDialog())
            {
                dialog.Title = openExisting ? "استيراد قاعدة بيانات" : "حفظ النسخة الاحتياطية";
                dialog.Filter = openExisting
                    ? "نسخة مشفّرة (*.esbak)|*.esbak"
                    : "Backup files (*.esbak)|*.esbak|All files (*.*)|*.*";
                dialog.DefaultExt = "esbak";
                dialog.AddExtension = true;
                dialog.CheckFileExists = openExisting;
                dialog.CheckPathExists = true;
                dialog.RestoreDirectory = true;
                dialog.ValidateNames = true;
                dialog.Multiselect = false;
                dialog.FileName = suggestedName;
                dialog.InitialDirectory = Environment.GetFolderPath(
                    Environment.SpecialFolder.DesktopDirectory);

                Form owner = new Form();
                owner.ShowInTaskbar = false;
                owner.TopMost = true;
                owner.WindowState = FormWindowState.Minimized;
                owner.Show();
                owner.Activate();

                DialogResult result = dialog.ShowDialog(owner);
                owner.Close();

                if (result != DialogResult.OK || string.IsNullOrWhiteSpace(dialog.FileName))
                {
                    return 2;
                }

                File.WriteAllText(outputPath, dialog.FileName, new UTF8Encoding(false));
                return 0;
            }
        }
    }
}
