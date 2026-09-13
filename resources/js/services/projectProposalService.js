import api from "@/api/axios";
const RESOURCE = "/project-proposals";
export default {
    async paginate(params={}) { return (await api.get(RESOURCE,{params})).data; },
    async options() { return (await api.get(`${RESOURCE}/options`)).data; },
    async find(id) { return (await api.get(`${RESOURCE}/${id}`)).data; },
    async store(payload) { return (await api.post(RESOURCE,payload)).data; },
    async update(id,payload) { return (await api.put(`${RESOURCE}/${id}`,payload)).data; },
    async remove(id) { return (await api.delete(`${RESOURCE}/${id}`)).data; },
    async submit(id) { return (await api.post(`${RESOURCE}/${id}/submit`)).data; },
    async review(id,decision,remarks=null) { return (await api.post(`${RESOURCE}/${id}/review`,{decision,remarks})).data; },
    async issueNtp(id) { return (await api.post(`${RESOURCE}/${id}/issue-ntp`)).data; },
    async downloadNtp(id) { return api.get(`${RESOURCE}/${id}/ntp/pdf`,{responseType:"blob"}); },
    async uploadDocument(id,payload) { return (await api.post(`${RESOURCE}/${id}/documents`,payload,{headers:{'Content-Type':'multipart/form-data'}})).data; },
    async downloadDocument(proposalId,documentId) { return api.get(`${RESOURCE}/${proposalId}/documents/${documentId}`,{responseType:'blob'}); },
    async deleteDocument(proposalId,documentId) { return (await api.delete(`${RESOURCE}/${proposalId}/documents/${documentId}`)).data; },
};
