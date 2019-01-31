/**
 *  Author: Ulrich Krause <eknori@eknori.de>
 *  URL: https://eknori.de
 *  License: APACHE 2
 * 
 * https://www.ibm.com/support/knowledgecenter/en/SSVRGU_10.0.1/basic/H_4_LOTUSSCRIPT_NOTES_CLASSES_REFERENCE.html
 */
SyntaxHighlighter.brushes.LotusScript = function()
{
  var keywords    ='Abs Access' + 
 ' ACos ActivateApp Alias And Any Append As Asc ASin Atn Atn2 Base Beep Bin Bin$ Binary Bind ByVal Call Case' + 
 ' CCur CDat CDbl ChDir ChDrive Chr Chr$ CInt Class CLng Close Command Command$ Compare Const Cos CSng CStr' + 
 ' CurDir CurDir$ CurDrive CurDrive$ Currency CVar DataType Date Date$ DateNumber DateValue Day Declare DefCur' + 
 ' DefDbl DefInt DefLng DefSng DefStr DefVar Delete Dim Dir Dir$ Do Double Else ElseIf End Environ Environ$ EOF' + 
 ' Eqv Erase Erl Err Error Error$ Evaluate Event Execute Exit Exp FALSE FileAttr FileCopy FileDateTime FileLen Fix' + 
 ' For ForAll Format Format$ Fraction FreeFile From Function Get GetFileAtt GoSub GoTo Hex Hex$ Hour If' + 
 ' IMEStatus Imp In Input Input$ InputB InputB$ InputBox InputBox$ InputBP InputBP$ InStr InStrB InStrBP Int Integer' + 
 ' Is IsA IsArray IsDate IsElement IsEmpty Lsi_info IsList IsNull IsNumeric IsObject IsScalar IsUnknown Kill LBound' + 
 ' LCase LCase$ Left Left$ LeftB LeftB$ LeftBP LeftBP$ Len LenB LenBP Let Lib Like Line List ListTag LMBCS Loc Lock LOF' + 
 ' Log Long Loop LSet LTrim LTrim$ Me MessageBox Mid Mid$ MidB MidB$ Minute MkDir msgbox Mod Month Name New Next NoCase' + 
 ' NoPitch Not NOTHING Now NULL Oct Oct$ On Open Option Or Output PI Pitch Preserve Print Private Property Public Put' + 
 ' Random Randomize Read ReDim Remove Reset Resume Return Right Right$ RightB RightB$ RightBP RightBP$ RmDir Rnd Round' + 
 ' RSet RTrim RTrim$ Second Seek Select SendKeys Set SetFileAtt Sgn Shared Shell Sin Single Space Space$ Spc Sqr Static' + 
 ' Step Stop Str Str$ StrCompare StrComp String String$ Sub Tab Tan Then Time Time$ TimeNumber Timer TimeValue To Today' + 
 ' Trim Trim$ TRUE Type TypeName UBound UCase UCase$ UChr UChr$ Uni Unicode Unlock Until Use UseLSX UString UString$ Val' + 
 ' Variant Weekday Wend While Width With Write Xor Year Yield';

 var methods ='.CreateJSONNavigator .GetElementByName .GetElementByPointer .GetFirstElement .GetNextElement .GetNthElement.PreferUTF8 .CreateHttpRequest .DeleteResource .Get .Post .Put .Patch .SetHeaderField .ResetHeaders .GetResponseHeaders .Maxredirects .Preferstrings' +
 ' .Responsecode .Timeoutsec .getIDVault .replace .getdatabase .isopen .explain .execute .parse .createdominoquery .GetUserIDFile .PutUserIDFile .SyncUserIDFile' +
 ' .ResetUserPassword .IsIDInVault .GetUserID .GetEncryptionKeys .GetUserName .Abbreviated .Abstract .Accept .AcceptCounter .ACL .ACLActivityLog' +
 ' .ACLImportOption .Activate .Add .AddCertifierToAddressBook .AddDatabase .AddDocument .AddEntry .AddGroupMembers .AddInternetCertificateToUser' +
 ' .AddNewLine .AddPageBreak .AddParameter .AddParticipant .Addr821 .Addr822Comment1 .Addr822Comment2 .Addr822Comment3 .Addr822LocalPart' +
 ' .Addr822Phrase .AddressBooks .AddRole .AddRow .AddServerToAddressBook .AddServerToCluster .AddTab .AddUserProfile .AddUserToAddressBook' +
 ' .AddValText .AddXMLDeclNode .AdjustDay .AdjustHour .AdjustMinute .AdjustMonth .AdjustSecond .AdjustYear .ADMD .AdministrationServer .Agents' +
 ' .Alarm .Alias .Aliases .Alignment .AllDocuments .AllEntries .AlternateColor .AltOrgUnit .AltOrgUnitLang .AMString .AppendChild .AppendData' +
 ' .AppendDocLink .AppendItemValue .AppendParagraphStyle .AppendRTItem .AppendStyle .AppendTable .AppendText .AppendToTextList' +
 ' .ApproveDeletePersonInDirectory .ApproveDeleteServerInDirectory .ApproveDesignElementDeletion .ApproveHostedOrgStorageDeletion' +
 ' .ApproveMailFileDeletion .ApproveMovedReplicaDeletion .ApproveNameChangeRetraction .ApproveRenamePersonInDirectory' +
 ' .ApproveRenameServerInDirectory .ApproveReplicaDeletion .ApproveResourceDeletion .AttachmentOmittedText .AttachVCard .AttributeName' +
 ' .Attributes .AttributeValue .Authors .AutoReload .AutoSendNotices .AutoUpdate .AvailableItems .AvailableNames .AvailableView .BackgroundColor' +
 ' .BarColor .BeginInsert .BeginSection .Blue .Bold .BoundaryEnd .BoundaryStart .BuildCollection .Bytes .CacheSize .CalendarDateTime' +
 ' .CalendarDateTimeEnd .Cancel .CanCreateDocuments .CanCreateLSOrJavaAgent .CanCreatePersonalAgent .CanCreatePersonalFolder' +
 ' .CanCreateSharedFolder .CanDeleteDocuments .Canonical .CanReplicateOrCopyDocuments .CaretCategory .CaretNoteID .Categories .Categorize' +
 ' .CertificateAuthorityOrg .CertificateExpiration .CertifierFile .CertifierIDFile .CertifierName .CertifierPassword .ChangeHTTPPassword' +
 ' .Charset .CheckAlarms .ChildCount .Clear .ClearAllTabs .ClearCollection .ClearHistory .ClearProperty .Click .Clone .Close' +
 ' .CloseMIMEEntities .CollapseAllSections .Color .ColorLegend .Column .ColumnCount .ColumnIndentLevel .ColumnNames .Columns .ColumnValues' +
 ' .Comment .Common .CommonOwner .CommonUserName .Compact .CompactWithOptions .CompileLotusScript .ComposeDocument .ComputeWithForm' +
 ' .ConfigureMailAgent .Contains .ContentAsText .ContentSubType .ContentType .ConvertMIME .ConvertNotesBitmapsToGIF .ConvertToMIME' +
 ' .ConvertToZone .Copy .CopyAllItems .CopyColumn .CopyItem .CopyItemToDocument .CopyToDatabase .Count .Counter .Country .Create .CreateACLEntry' +
 ' .CreateAdministrationProcess .CreateAttributeNode .CreateAttributeNodeNS .CreateCDATASectionNode .CreateChildEntity .CreateColorObject' +
 ' .CreateColumn .CreateCommentNode .CreateCopy .Created .CreateDatabase .CreateDateRange .CreateDateTime .CreateDocument .CreateDocumentFragmentNode' +
 ' .reateDocumentNode .CreateDOMParser .CreateDXLExporter .CreateDXLImporter .CreateElementNode .CreateElementNodeNS .CreateEntityReferenceNode' +
 ' .CreateEntry .CreateEntryFrom .CreateFromTemplate .CreateFTIndex .CreateHeader .CreateLog .CreateMailDb .CreateMIMEEntity .CreateName' +
 ' .CreateNavigator .CreateNewsletter .CreateNotationNode .CreateNoteCollection .CreateObject .CreateOutline .CreateParentEntity' +
 ' .CreateProcessingInstructionNode .CreateRange .CreateRegistration .CreateReplica .CreateReplyMessage .CreateRichTextItem' +
 ' .CreateRichTextParagraphStyle .CreateRichTextStyle .CreateSAXParser .CreateStream .CreateTextNode .CreateTimer .CreateView .CreateViewNav' +
 ' .CreateViewNavFrom .CreateViewNavFromAllUnread .CreateViewNavFromCategory .CreateViewNavFromChildren .CreateViewNavFromDescendants' +
 ' .CreateViewNavMaxLevel .CreateXMLDeclNode .CreateXSLTransformer .CrossCertify .CurrencyDigits .CurrencySymbol .CurrentAccessLevel .CurrentAgent' +
 ' .CurrentCalendarDateTime .CurrentDatabase .CurrentDocument .CurrentField .CurrentItem .CurrentMatch .CurrentMatches .CurrentName .CurrentView' +
 ' .Cut .CutoffDate .CutoffDelete .CutoffInterval .Data .Database .DateFmt .DateOnly .DateSep .DateTimeValue .DbReplicaID .DecimalSep .Decline' +
 ' .DeclineCounter .DecodeContent .DelayUpdates .Delegate .DeleteData .DeleteDocument .DeleteEntry .DeleteGroup .DeleteIDOnServer .DeleteReplicas' +
 ' .DeleteRole .DeleteServer .DeleteUser .DescendantCount .Description .DeselectAll .DesignImportOption .DesignTemplateName .Destination .DialogBox' +
 ' .DialogBoxCanceled .Disabled .DisableRole .DisplayAlternateNames .DisplayCheckboxes .DisplayComment .DisplayHoursPerDay .DisplayMeetingSuggestions' +
 ' .DisplayParticipantStatus .DisplayPeople .DisplayResources .DisplayRooms .DisplayTwisties .DoctypeSYSTEM .Document .DocumentContext' +
 ' .DocumentElement .DocumentImportOption .Documents .DocUNID .DoNamespaces .DontSendLocalSecurityUpdates .DoScore .DoSubject .DoVerb' +
 ' .EditDocument .EditMode .EditProfile .EffectiveUserName .Effects .EmbeddedObjects .EmbedObject .EnableAlarms .Enabled .EnableFolder' +
 ' .EnableRole .EncodeContent .Encoding .Encrypt .EncryptionKeys .EncryptOnSend .EndDateTime .EndInsert .EndSection .EnforceUniqueShortName' +
 ' .Entering .EntriesProcessed .EntryClass .EntryCount .Evaluate .Exiting .ExitOnFirstFatalError .ExpandAllSections .ExpandEntityReferences' +
 ' .Expiration .Export .ExtractFile .FieldAppendText .FieldClear .FieldContains .FieldGetText .FieldHelp .Fields .FieldSetText .FileFormat' +
 ' .FileName .FilePath .FileSize .FindAndReplace .FindFirstElement .FindFirstMatch .FindFirstName .FindFirstString .FindFreeTimeDialog' +
 ' .FindFreeTimeDialogEx .FindGroupInDomain .FindLastElement .FindNextElement .FindNextMatch .FindNextName .FindNextString .FindNthElement' +
 ' .FindNthMatch .FindNthName .FindServerInDomain .FindString .FindUserInDomain .FirstChild .FirstLineLeftMargin .FitBelowFields .FitToWindow' +
 ' .Fixup .Folder .FolderReferences .FolderReferencesEnabled .FontColor .FontFace .FontPointSize .FontSize .FontStyle .ForceNoteFormat .FormatDocument' +
 ' .FormatMsgWithDoclinks .Forms .Formula .FormUsers .Forward .FrameText .FreeLookupBuffer .FreeTimeSearch .FTDomainSearch .FTIndexFrequency' +
 ' .FTSearch .FTSearchRange .FTSearchScore .Generation .GetAgent .GetAllDocumentsByKey .GetAllEntriesByKey .GetAllReadDocuments .GetAllReadEntries' +
 ' .GetAllUnreadDocuments .GetAllUnreadEntries .GetAsDocument .GetAttachment .GetAttribute .GetAttributeNode .GetAttributeNodeNS .GetAttributeNS' +
 ' .GetChild .GetColumn .GetComponentViewPreference .GetContentAsBytes .GetContentAsText .GetCurrent .GetCurrentDatabase .GetDatabase .GetDbDirectory' +
 ' .GetDirectory .GetDocument .GetDocumentByID .GetDocumentByKey .GetDocumentByUNID .GetDocumentByURL .GetElement .GetElementsByTagName' +
 ' .GetElementsByTagNameNS .GetEmbeddedObject .GetEntityAsText .GetEntries .GetEntry .GetEntryByKey .GetEntryByNoteID .GetEntryByUNID' +
 ' .GetEnvironmentString .GetEnvironmentValue .GetFieldType .GetFirst .GetFirstChildEntity .GetFirstDatabase .GetFirstDocument' +
 ' .GetFirstElement .GetFirstEntry .GetFirstImportedNoteID .GetFirstItem .GetFirstItemValue .GetFirstNoteID .GetForm .GetFormattedText' +
 ' .GetHeaderVal .GetHeaderValAndParams .GetIDFromServer .GetItem .GetItemValue .GetItemValueCustomDataBytes .GetItemValueDateTimeArray .GetLast' +
 ' .GetLastDocument .GetLastElement .GetLastEntry .GetListOfTunes .GetMailInfo .GetMIMEEntity .GetModifiedDocuments .GetName .GetNewInvitations' +
 ' .GetNext .getNext .method .GetNextCategory .GetNextDatabase .GetNextDocument .GetNextElement .GetNextEntity .GetNextEntry .GetNextImportedNoteID' +
 ' .GetNextItemValue .GetNextNoteID .GetNextSibling .GetNotesFont .GetNoticeByUNID .GetNotices .GetNth .getNth .method .GetNthDocument .GetNthElement' +
 ' .GetNthEntry .GetNthHeader .GetNthItemValue .GetObject .GetOption .GetOutline .GetOutstandingInvitations .GetParamVal .GetParent .GetParentDocument' +
 ' .GetParentEntity .GetParticipants .GetPerformanceDocument .GetPos .GetPosition .GetPrev .GetPrevCategory .GetPrevDocument .GetPrevEntity' +
 ' .GetPrevEntry .GetPrevSibling .GetProfileDocCollection .GetProfileDocument .GetProperty .GetPropertyBroker .GetPropertyValue .GetRead' +
 ' .GetReceivedItemText .GetScheduleData .GetSchedulerObject .GetSelectedText .GetSomeHeaders .GetType .GetUnformattedText .GetURLHeaderInfo' +
 ' .GetUserInfo .GetUserPolicySettings .GetValue .GetValueCustomDataBytes .GetValueDateTimeArray .GetView .Given .GMTTime .GotoBottom .GotoChild' +
 ' .GotoEntry .GotoField .GotoFirst .GotoFirstDocument .GotoLast .GotoLastDocument .GotoNext .GotoNextCategory .GotoNextDocument .GotoNextField' +
 ' .GotoNextSibling .GotoParent .GotoPos .GotoPrev .GotoPrevCategory .GotoPrevDocument .GotoPrevField .GotoPrevSibling .GotoTop .GrantAccess .Green' +
 ' .GroupAuthorizationOnly .GroupList .HasChildNodes .HasChildren .HasEmbedded .HashPassword .HasItem .HasProperty .HasRunSinceModified' +
 ' .HeaderAlignment .HeaderFontColor .HeaderFontFace .HeaderFontPointSize .HeaderFontStyle .HeaderLines .HeaderName .HeaderObjects .Headers' +
 ' .HiddenChars .HideFormula .HorzScrollBar .HotSpotText .HotSpotTextStyle .HttpURL .Hue .IDType .IgnoreDeletes .IgnoreDestDeletes .ImagesText .Import' +
 ' .ImportedNoteCount .IndentLevel .Initialize .InitializeUsingNotesUserName .Initials .InPreviewPane .InputPropertyContext .InputValidationOption' +
 ' .InsertData .InsertText .InterLineSpacing .International .InternetLevel .Intersect .Interval .InViewEdit .IsAccentSensitiveSort .IsActivatable' +
 ' .IsAdminNames .IsAdminReaderAuthor .IsAdminServer .IsAuthors .IsCalendar .IsCaseSensitiveSort .IsCategorized .IsCategory' +
 ' .IsCertificateAuthorityAvailable .IsClusterReplication .IsConfigurationDirectory .IsConflict .IsCurrencySpace .IsCurrencySuffix' +
 ' .IsCurrencyZero .IsCurrentAccessPublicReader .IsCurrentAccessPublicWriter .IsDateDMY .IsDateMDY .IsDateYMD .IsDefault .IsDefaultView' +
 ' .IsDeleted .IsDesignLockingEnabled .IsDirectoryCatalog .IsDocument .IsDocumentLockingEnabled .IsDST .IsEmbeddedInsideWCT .IsEnabled' +
 ' .IsEncrypted .IsEOS .IsExpanded .IsExtendedAccess .IsField .IsFolder .IsFontBold .IsFontItalic .IsFontStrikethrough .IsFontUnderline' +
 ' .IsFormula .IsFTIndexed .IsGroup .IsHeaderFontBold .IsHeaderFontItalic .IsHeaderFontStrikethrough .IsHeaderFontUnderline .IsHidden' +
 ' .IsHiddenFromNotes .IsHiddenFromWeb .IsHideDetail .IsHierarchical .IsIcon .IsIncludeACL .IsIncludeAgents .IsIncludeDocuments' +
 ' .IsIncludeForms .IsIncludeFormulas .IsInCompositeApp .IsInMultiDbIndexing .IsInput .IsInService .IsInThisDB .IsLink .IsModified' +
 ' .IsMultiDbSearch .IsNames .IsNewDoc .IsNewNote .IsNorthAmerican .IsNotesAgent .IsNull .IsNumberAttribParens .IsNumberAttribPercent' +
 ' .IsNumberAttribPunctuated .IsOnServer .IsOpen .IsPendingDelete .IsPerson .IsPrivate .IsPrivateAddressBook .IsProfile' +
 ' .IsProhibitDesignRefresh .IsProtected .IsPublic .IsPublicAddressBook .IsPublicReader .IsPublicWriter .IsReaders .IsReadOnly .IsResize' +
 ' .IsResortAscending .IsResortDescending .IsResortToView .IsResponse .IsRoamingUser .IsRoleEnabled .IsSecondaryResort .IsSecondaryResortDescending' +
 ' .IsServer .IsShowTwistie .IsSigned .IsSortDescending .IsSorted .IsSpecified .IsSubForm .IsSummary .IsTime24Hour .IsTotal .IsUIDocOpen' +
 ' .IsUsingJavaElement .IsValid .IsValidDate .IsWebAgent .Italic .ItemName .Items .KeepSelectionFocus .Key .Keyword .Label .Language .LastAccessed' +
 ' .LastBuildTime .LastChild .LastExitStatus .LastFixup .LastFTIndexed .LastModified .LastRun .LeftMargin .Length .Level .LimitMatches .LimitRevisions' +
 ' .LimitUpdatedBy .ListInDbCatalog .ListSep .LocalName .LocalTime .Lock .LockHolders .LockProvisional .Log .LogAction .LogActions .LogComment' +
 ' .LogError .LogErrors .LogEvent .LookupAllNames .LookupNames .LSGMTTime .LSLocalTime .Luminance .MailACLManager .MailCreateFTIndex' +
 ' .MailInternetAddress .MailOwnerAccess .MailQuotaSizeLimit .MailQuotaWarningThreshold .MailReplicaServers .MailSystem .MailTemplateName' +
 ' .MakeResponse .Managers .MarkAllRead .MarkAllUnread .MarkForDelete .MarkRead .MarkUnread .MatchLocated .MaxLevel .MaxSize .MeetingIndicator' +
 ' .Merge .Message .MIMEOption .MinPasswordLength .ModifiedSinceSaved .MoveEntry .MoveMailUser .MoveReplica .MoveRoamingUser' +
 ' .MoveUserInHierarchyComplete .MoveUserInHierarchyRequest .Name .NamedElement .NameLocated .NameObject .NameOfProfile .NameSpace' +
 ' .NamespaceURI .NavBarSetText .NavBarSpinnerStart .NavBarSpinnerStop .Navigator .NextSibling .NodeName .NodeType .NodeValue .NoIDFile .NoteID' +
 ' .NotesBuildVersion .NotesColor .NotesFont .NotesURL .NotesVersion .NumActions .NumberAttrib .NumberDigits .NumberFormat .NumberOfChildNodes' +
 ' .NumberOfEntries .NumErrors .Object .ObjectExecute .OLEObjectOmittedText .OmitItemNames .OmitMiscFileObjects .OmitOLEObjects' +
 ' .OmitRichtextAttachments .OmitRichtextPictures .OnBehalfOf .OnBlur .OnChange .OnFocus .OnHelp .OnIntervalChange .OnLoad .Onselect .OnSubmit' +
 ' .OnSuggestionsAvailable .OnUnload .Open .OpenAgentLog .OpenByReplicaID .OpenDatabase .OpenDatabaseByReplicaID .OpenDatabaseIfModified' +
 ' .OpenFileDialog .OpenFileLog .OpenFrameSet .OpenIfModified .OpenMail .OpenMailDatabase .OpenMailLog .OpenNavigator .OpenNotesLog .OpenPage' +
 ' .OpenURLDb .OpenView .OpenWithFailover .Organization .OrgDirectoryPath .OrgUnit .OrgUnit1 .OrgUnit2 .OrgUnit3 .OrgUnit4 .OutlineReload .Output' +
 ' .OutputDOCTYPE .OverwriteCheckEnabled .OverwriteFile .Owner .Pagination .ParameterDocID .Parent .ParentDatabase .ParentDocumentUNID .ParentNode' +
 ' .ParentView .Parse .PartialMatches .PassThruHTML .Paste .PercentUsed .PickListCollection .PickListStrings .PictureOmittedText .Platform .PlayTune' +
 ' .PMString .PolicyName .Position .PostDocumentDelete .PostDOMParse .PostDragDrop .PostDropToArchive .PostEntryResize .PostModeChange .PostOpen' +
 ' .PostPaste .PostRecalc .PostSave .PostSend .Preamble .Prefix .PreviewDocLink .PreviewParentDoc .PreviousSibling .Print .Priority .PRMD .Process' +
 ' .ProgramName .ProhibitDesignUpdate .Prompt .ProtectReaders .ProtectUsers .PublicID .Publish .PutAllInFolder .PutInFolder .Query .QueryAccess' +
 ' .QueryAccessPrivileges .QueryAccessRoles .QueryAddToFolder .QueryClose .QueryDocumentDelete .QueryDocumentUndelete .QueryDragDrop' +
 ' .QueryDropToArchive .QueryEntryResize .QueryModeChange .QueryOpen .QueryOpenDocument .QueryPaste .QueryRecalc .QuerySave .QuerySend .Read .Readers' +
 ' .ReadRange .ReadRangeMask1 .ReadRangeMask2 .ReadText .ReadXLotusPropsOutputLevel .Recertify .RecertifyServer .RecertifyUser .Red .Refresh' +
 ' .RefreshHideFormulas .RefreshParentNote .RegionDoubleClick .RegisterNewCertifier .RegisterNewServer .RegisterNewUser .RegistrationLog' +
 ' .RegistrationServer .Reload .ReloadWindow .Remove .RemoveACLEntry .RemoveAll .RemoveAllFromFolder .RemoveAttribute .RemoveAttributeNode' +
 ' .RemoveAttributeNS .RemoveCancelled .RemoveChild .RemoveColumn .RemoveEntry .RemoveFromFolder .RemoveFTIndex .RemoveItem .RemoveLinkage' +
 ' .RemoveParticipants .RemovePermanently .RemoveRow .RemoveServerFromCluster .RenameGroup .RenameNotesUser .RenameRole .RenameWebUser' +
 ' .RenderToRTItem .ReplaceChild .ReplaceData .ReplaceDbProperties .ReplaceItemValue .ReplaceItemValueCustomDataBytes .ReplicaID' +
 ' .ReplicaRequiredForReplaceOrUpdate .Replicate .ReplicationInfo .RequestInfo .Reset .ResetUserPassword .Resolve .ResortToViewName' +
 ' .ResortView .Responses .RestrictToItemNames .RevokeAccess .RichTextOption .RightMargin .RightToLeft .RoamingCleanupPeriod .RoamingCleanupSetting' +
 ' .RoamingServer .RoamingSubdir .Roles .Row .RowCount .RowLabels .RowLines .Ruler .Run .RunOnServer .RunReadOnly .Saturation .Save .SavedData' +
 ' .SaveFileDialog .SaveMessageOnSend .SaveNewVersion .SaveToDisk .SAX_Characters .SAX_EndDocument .SAX_EndElement .SAX_Error .SAX_FatalError' +
 ' .SAX_IgnorableWhiteSpace .SAX_NotationDecl .SAX_ProcessingInstruction .SAX_ResolveEntity .SAX_StartDocument .SAX_StartElement' +
 ' .SAX_UnparsedEntityDecl .SAX_Warning .ScheduleGridStart .SchedulerName .SchemaLocation .Search .SearchAllDirectories .SecondaryResortColumnIndex' +
 ' .SelectACL .SelectActions .SelectAgents .SelectAll .SelectAllAdminNotes .SelectAllCodeElements .SelectAllDataNotes .SelectAllDesignElements' +
 ' .SelectAllFormatElements .SelectAllIndexElements .SelectAllNotes .SelectDatabaseScript .SelectDataConnections .SelectDocument .SelectDocuments' +
 ' .SelectFolders .SelectForms .SelectFrameSets .SelectHelpAbout .SelectHelpIndex .SelectHelpUsing .SelectIcon .SelectImageResources' +
 ' .SelectionFormula .SelectJavaResources .SelectMiscCodeElements .SelectMiscFormatElements .SelectMiscIndexElements .SelectNavigators' +
 ' .SelectOutlines .SelectPages .SelectProfiles .SelectReplicationFormulas .SelectScriptLibraries .SelectSharedFields .SelectStyleSheetResources' +
 ' .SelectSubforms .SelectViews .Send .SendConsoleCommand .SendUpdatedInfo .SentByAgent .Serialize .Server .ServerHint .ServerName .SetAction' +
 ' .SetAliases .SetAlternateColor .SetAnyDate .SetAnyTime .SetAttribute .SetAttributeNode .SetAttributeNodeNS .SetAttributeNS .SetBarColor' +
 ' .SetBegin .SetCharOffset .SetColor .SetContentFromBytes .SetContentFromText .SetCurrentLocation .SetEnd .SetEnvironmentVar .SetHeaderVal' +
 ' .SetHeaderValAndParams .SetHotSpotTextStyle .SetHSL .SetInput .SetNamedElement .SetNoteLink .SetNow .SetOption .SetOutput .SetParamVal .SetPosition' +
 ' .SetPositionAtEnd .SetPropertyValue .SetRGB .SetServerDirectoryAssistanceSettings .SetStyle .SetStyleSheet .SetTab .SetTabs .SetTargetFrame' +
 ' .SetTitleStyle .SetURL .SetUserPasswordSettings .SetValueCustomDataBytes .ShortName .SiblingCount .Sign .SignDatabaseWithServerID .Signer' +
 ' .SignOnSend .SinceTime .Size .SizeQuota .SizeWarning .Source .Spacing .SpacingAbove .SpacingBelow .SpellCheck .SplitText .StampAll .StampAllMulti' +
 ' .Standalone .StartDateTime .StoreIDInAddressBook .StoreIDInMailfile .StrikeThrough .Style .SubjectItemName .SubstringData .Subtract .Surname' +
 ' .SwitchToID .SynchInternetPassword .SystemID .Tabs .TagName .Target .TemplateName .TentativelyAccept .Text .TextParagraph .TextRun .ThousandsSep' +
 ' .TimeDateFmt .TimeDifference .TimeDifferenceDouble .TimeFmt .TimeOnly .TimeSep .TimeZone .TimeZoneFmt .Title .TitleStyle .Today .Tomorrow' +
 ' .TopLevelEntryCount .Transform .Trigger .Truncate .TrustedOnly .Type .Typename .UID .UncompressAttachments .UndeleteExpireTime .Underline' +
 ' .UNID .UniformAccess .UniversalID .UnknownTokenLogOption .UnLock .UnprocessedDocuments .UnprocessedFTSearch .UnprocessedFTSearchRange' +
 ' .UnprocessedSearch .UntilTime .Update .UpdateAddressBook .UpdateAll .UpdateFTIndex .UpdateProcessedDoc .UpgradeUserToHierarchical .URL' +
 ' .URLDatabase .URLOpen .UseCertificateAuthority .UseContextServer .UseHideFormula .UseLSX .UserGroupNameList .UserName .UserNameList' +
 ' .UserNameObject .UserType .ValidationStyle .ValueLength .Value .Values .Verbs .Verifier .VerifyPassword .Version .ViewAlias .ViewInheritedFrom' +
 ' .ViewInheritedName .ViewName .ViewRebuild .ViewRefresh .Views .ViewUNID .Width .WindowTitle .Write .WriteText .Yesterday .ZoneTime .GetThreadInfo .Lsi_Info';

  var classes    ='Field Navigator NotesACL NotesACLEntry NotesAdministrationProcess' +
 ' NotesAgent NotesColorObject NotesDatabase NotesDateRange NotesDateTime NotesDbDirectory' +
 ' NotesDocument NotesDocumentCollection NotesDOMAttributeNode NotesDOMCDATASectionNode' +
 ' NotesDOMCharacterDataNode NotesDOMCommentNode NotesDOMDocumentFragmentNode NotesDOMDocumentNode' +
 ' NotesDOMDocumentTypeNode NotesDOMElementNode NotesDOMEntityNode NotesDOMEntityReferenceNode' +
 ' NotesDOMNamedNodeMap NotesDOMNode NotesDOMNodeList NotesDOMNotationNode NotesDOMParser' +
 ' NotesDOMProcessingInstructionNode NotesDOMTextNode NotesDOMXMLDeclNode NotesDXLExporter' +
 ' NotesDXLImporter NotesEmbeddedObject NotesForm NotesInternational NotesItem NotesLog' +
 ' NotesMIMEEntity NotesMIMEHeader NotesName NotesNewsletter NotesNoteCollection NotesOutline' +
 ' NotesOutlineEntry NotesRegistration NotesReplication NotesReplicationEntry NotesRichTextDocLink' +
 ' NotesRichTextItem NotesRichTextNavigator NotesRichTextParagraphStyle NotesRichTextRange' +
 ' NotesRichTextSection NotesRichTextStyle NotesRichTextTab NotesRichTextTable NotesSAXAttributeList' +
 ' NotesSAXException NotesSAXParser NotesSession NotesStream NotesTimer NotesUIDatabase NotesUIDocument' +
 ' NotesUIScheduler NotesUIView NotesUIWorkspace NotesView NotesViewColumn NotesViewEntry' +
 ' NotesViewEntryCollection NotesViewNavigator NotesXMLProcessor NotesXSLTranformer' +
 ' NotesDominoQuery NotesHttpRequest NotesJsonArray NotesJsonObject NotesJsonElement NotesJsonNavigator NotesIDVault NotesUserId';
 
  this.regexList = [
      { regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString,   css:'string' },
      { regex: new RegExp(this.getKeywords(classes),'gmi'),              css:'lsClass' },
      { regex: new RegExp(this.getKeywords(methods),'gmi'),		           css:'lsMethod' },
      { regex: new RegExp(this.getKeywords(keywords),'gmi'),             css:'lsKeyword' },
      { regex: new RegExp('%REM[\\s\\S]*?%END REM','gi'),                css:'lsComment' },
      { regex: new RegExp('\'.*[\\s\\S]*?','gi'),                        css:'lsComment' },
      { regex: new RegExp('%\\S{2,}','gi'),                              css:'lsDirective' },
      { regex: new RegExp('%end\\s\\S{2,}','gi'),                        css:'lsDirective' },
      //{ regex: new RegExp('\|[\\s\\S]*?\|','gmi'),                        css:'string' },
      ];
};
SyntaxHighlighter.brushes.LotusScript.prototype = new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.LotusScript.aliases  = ['lotusscript','ls'];
