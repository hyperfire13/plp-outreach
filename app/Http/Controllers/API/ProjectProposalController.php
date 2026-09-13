<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectProposal\StoreProjectProposalRequest;
use App\Http\Requests\ProjectProposal\UpdateProjectProposalRequest;
use App\Models\ProjectProposal;
use App\Services\ProjectProposalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ProjectProposalDocument;
use Illuminate\Support\Facades\Storage;

class ProjectProposalController extends Controller
{
    public function __construct(private readonly ProjectProposalService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny',ProjectProposal::class);
        $filters=$request->validate(['search'=>['nullable','string','max:255'],'status'=>['nullable',Rule::in(ProjectProposal::STATUSES)],'per_page'=>['nullable','integer','min:1','max:100']]);
        return response()->json(['message'=>'Project proposals retrieved successfully.','data'=>$this->service->paginate($request->user(),$filters)]);
    }
    public function options(Request $request): JsonResponse { $this->authorize('viewAny',ProjectProposal::class); return response()->json(['message'=>'Project proposal options retrieved successfully.','data'=>$this->service->options($request->user())]); }
    public function store(StoreProjectProposalRequest $request): JsonResponse { return response()->json(['message'=>'Project proposal created successfully.','data'=>$this->service->store($request->user(),$request->validated())],201); }
    public function show(ProjectProposal $projectProposal): JsonResponse { $this->authorize('view',$projectProposal); return response()->json(['message'=>'Project proposal retrieved successfully.','data'=>$this->withPermissions($projectProposal,request())]); }
    public function update(UpdateProjectProposalRequest $request,ProjectProposal $projectProposal): JsonResponse { return response()->json(['message'=>'Project proposal updated successfully.','data'=>$this->service->update($projectProposal,$request->validated())]); }
    public function destroy(ProjectProposal $projectProposal): JsonResponse { $this->authorize('delete',$projectProposal); $this->service->delete($projectProposal); return response()->json(['message'=>'Project proposal deleted successfully.']); }
    public function submit(Request $request,ProjectProposal $projectProposal): JsonResponse { $this->authorize('submit',$projectProposal); return response()->json(['message'=>'Project proposal submitted for immediate head notation.','data'=>$this->service->submit($projectProposal)]); }
    public function review(Request $request,ProjectProposal $projectProposal): JsonResponse
    {
        $this->authorize('review',$projectProposal);
        $data=$request->validate(['decision'=>['required',Rule::in(['approve','revision','reject'])],'remarks'=>['nullable','required_if:decision,revision,reject','string','max:5000']]);
        return response()->json(['message'=>'Review decision recorded successfully.','data'=>$this->service->review($projectProposal,$request->user(),$data['decision'],$data['remarks']??null)]);
    }
    public function issueNtp(Request $request,ProjectProposal $projectProposal): JsonResponse { $this->authorize('issueNtp',$projectProposal); return response()->json(['message'=>'Notice to Proceed issued successfully.','data'=>$this->service->issueNtp($projectProposal,$request->user())]); }
    public function downloadNtp(ProjectProposal $projectProposal) { $this->authorize('view',$projectProposal); abort_unless($projectProposal->noticeToProceed,404,'Notice to Proceed has not been issued.'); $proposal=$this->service->find($projectProposal); return Pdf::loadView('pdf.notice-to-proceed',compact('proposal'))->download("{$proposal->noticeToProceed->ntp_number}.pdf"); }
    public function uploadDocument(Request $request,ProjectProposal $projectProposal): JsonResponse
    {
        $this->authorize('update',$projectProposal);
        $data=$request->validate(['document_type'=>['required',Rule::in(['moa','letter','budget','workplan','other'])],'file'=>['required','file','mimes:pdf,doc,docx,xls,xlsx','max:10240']]);
        $file=$data['file']; $disk=config('filesystems.default'); $path=$file->store("project-proposals/{$projectProposal->id}",$disk);
        $document=$projectProposal->documents()->create(['document_type'=>$data['document_type'],'original_name'=>$file->getClientOriginalName(),'storage_path'=>$path,'disk'=>$disk,'mime_type'=>$file->getMimeType()?:'application/octet-stream','file_size'=>$file->getSize(),'uploaded_by'=>$request->user()->id]);
        return response()->json(['message'=>'Supporting document uploaded successfully.','data'=>$document],201);
    }
    public function downloadDocument(ProjectProposal $projectProposal,ProjectProposalDocument $document)
    {
        $this->authorize('view',$projectProposal); abort_unless($document->project_proposal_id===$projectProposal->id,404); abort_unless(Storage::disk($document->disk)->exists($document->storage_path),404,'File not found.');
        return Storage::disk($document->disk)->download($document->storage_path,$document->original_name);
    }
    public function deleteDocument(ProjectProposal $projectProposal,ProjectProposalDocument $document): JsonResponse
    {
        $this->authorize('update',$projectProposal); abort_unless($document->project_proposal_id===$projectProposal->id,404); Storage::disk($document->disk)->delete($document->storage_path); $document->delete(); return response()->json(['message'=>'Supporting document deleted successfully.']);
    }

    private function withPermissions(ProjectProposal $proposal,Request $request): ProjectProposal
    {
        $proposal=$this->service->find($proposal); $user=$request->user();
        $proposal->setAttribute('permissions',['update'=>$user->can('update',$proposal),'delete'=>$user->can('delete',$proposal),'submit'=>$user->can('submit',$proposal),'review'=>$user->can('review',$proposal),'issue_ntp'=>$user->can('issueNtp',$proposal)]);
        return $proposal;
    }
}
