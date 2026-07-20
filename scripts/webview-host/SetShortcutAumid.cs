using System;
using System.IO;
using System.Runtime.InteropServices;

internal static class SetShortcutAumid
{
    private static readonly Guid CLSID_ShellLink = new Guid("00021401-0000-0000-C000-000000000046");
    private static readonly Guid IID_IShellLinkW = new Guid("000214F9-0000-0000-C000-000000000046");
    private static readonly Guid IID_IPersistFile = new Guid("0000010B-0000-0000-C000-000000000046");
    private static readonly Guid IID_IPropertyStore = new Guid("886D8EEB-8CF2-4446-8D02-CDBA1DBDCF99");

    [StructLayout(LayoutKind.Sequential, Pack = 4)]
    private struct PropertyKey
    {
        public Guid fmtid;
        public uint pid;
    }

    [StructLayout(LayoutKind.Sequential)]
    private struct PropVariant
    {
        public ushort vt;
        public ushort wReserved1;
        public ushort wReserved2;
        public ushort wReserved3;
        public IntPtr pszVal;
        public IntPtr pad;
    }

    [ComImport, Guid("000214F9-0000-0000-C000-000000000046"), InterfaceType(ComInterfaceType.InterfaceIsIUnknown)]
    private interface IShellLinkW
    {
        void GetPath([Out, MarshalAs(UnmanagedType.LPWStr)] System.Text.StringBuilder pszFile, int cch, IntPtr pfd, uint fFlags);
        void GetIDList(out IntPtr ppidl);
        void SetIDList(IntPtr pidl);
        void GetDescription([Out, MarshalAs(UnmanagedType.LPWStr)] System.Text.StringBuilder pszName, int cch);
        void SetDescription([MarshalAs(UnmanagedType.LPWStr)] string pszName);
        void GetWorkingDirectory([Out, MarshalAs(UnmanagedType.LPWStr)] System.Text.StringBuilder pszDir, int cch);
        void SetWorkingDirectory([MarshalAs(UnmanagedType.LPWStr)] string pszDir);
        void GetArguments([Out, MarshalAs(UnmanagedType.LPWStr)] System.Text.StringBuilder pszArgs, int cch);
        void SetArguments([MarshalAs(UnmanagedType.LPWStr)] string pszArgs);
        void GetHotkey(out short pwHotkey);
        void SetHotkey(short wHotkey);
        void GetShowCmd(out int piShowCmd);
        void SetShowCmd(int iShowCmd);
        void GetIconLocation([Out, MarshalAs(UnmanagedType.LPWStr)] System.Text.StringBuilder pszIconPath, int cch, out int piIcon);
        void SetIconLocation([MarshalAs(UnmanagedType.LPWStr)] string pszIconPath, int iIcon);
        void SetRelativePath([MarshalAs(UnmanagedType.LPWStr)] string pszPathRel, uint dwReserved);
        void Resolve(IntPtr hwnd, uint fFlags);
        void SetPath([MarshalAs(UnmanagedType.LPWStr)] string pszFile);
    }

    [ComImport, Guid("0000010b-0000-0000-C000-000000000046"), InterfaceType(ComInterfaceType.InterfaceIsIUnknown)]
    private interface IPersistFile
    {
        void GetClassID(out Guid pClassID);
        [PreserveSig] int IsDirty();
        void Load([MarshalAs(UnmanagedType.LPWStr)] string pszFileName, uint dwMode);
        void Save([MarshalAs(UnmanagedType.LPWStr)] string pszFileName, [MarshalAs(UnmanagedType.Bool)] bool fRemember);
        void SaveCompleted([MarshalAs(UnmanagedType.LPWStr)] string pszFileName);
        void GetCurFile([MarshalAs(UnmanagedType.LPWStr)] out string ppszFileName);
    }

    [ComImport, Guid("886D8EEB-8CF2-4446-8D02-CDBA1DBDCF99"), InterfaceType(ComInterfaceType.InterfaceIsIUnknown)]
    private interface IPropertyStore
    {
        void GetCount(out uint cProps);
        void GetAt(uint iProp, out PropertyKey pkey);
        void GetValue(ref PropertyKey key, out PropVariant pv);
        void SetValue(ref PropertyKey key, ref PropVariant pv);
        void Commit();
    }

    [DllImport("ole32.dll")]
    private static extern void PropVariantClear(ref PropVariant pvar);

    private static void Main(string[] args)
    {
        if (args.Length < 2)
        {
            Console.WriteLine("Usage: SetShortcutAumid <lnk> <aumid>");
            return;
        }

        string lnk = args[0];
        string aumid = args[1];
        if (!File.Exists(lnk))
        {
            throw new FileNotFoundException(lnk);
        }

        Type t = Type.GetTypeFromCLSID(CLSID_ShellLink);
        object link = Activator.CreateInstance(t);
        var pf = (IPersistFile)link;
        pf.Load(lnk, 0);

        var store = (IPropertyStore)link;
        var key = new PropertyKey
        {
            fmtid = new Guid("9F4C2855-9F79-4B39-A8D0-E1D42DE1D5F3"),
            pid = 5, // PKEY_AppUserModel_ID
        };

        var pv = new PropVariant();
        pv.vt = 31; // VT_LPWSTR
        pv.pszVal = Marshal.StringToCoTaskMemUni(aumid);
        store.SetValue(ref key, ref pv);
        store.Commit();
        PropVariantClear(ref pv);

        pf.Save(lnk, true);
        Marshal.ReleaseComObject(link);
        Console.WriteLine("AUMID_OK " + lnk);
    }
}
